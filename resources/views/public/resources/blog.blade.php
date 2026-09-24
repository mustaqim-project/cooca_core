@extends('layouts.public_marketing')

@section('title', 'Pusat Edukasi & Kurikulum Bisnis UMKM | Panduan Finansial, POS, & Otomasi - COOCA')
@section('description', 'Pusat kurikulum, panduan operasional, dan wawasan bisnis UMKM Indonesia. Pelajari strategi arus kas, perhitungan HPP presisi, teknik kasir POS cepat, dan otomasi pelanggan.')
@section('og_title', 'Pusat Edukasi & Kurikulum Bisnis UMKM | COOCA')
@section('og_description', 'Kurikulum bisnis praktis dan panduan operasional toko, kafe, bengkel, serta wirausaha mandiri di Indonesia.')
@section('canonical', route('public.resources.blog'))
@section('og_type', 'website')
@section('keywords', 'edukasi bisnis umkm, wawasan bisnis indonesia, tips pembukuan toko, belajar hitung hpp, strategi kasir pos, panduan wirausaha mandiri, otomasi whatsapp bisnis')

@push('seo')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Pusat Edukasi & Kurikulum Bisnis UMKM COOCA",
        "description": "Kurikulum dan basis pengetahuan operasional bisnis terstruktur untuk pemilik usaha UMKM Indonesia.",
        "url": "{{ route('public.resources.blog') }}",
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
        showSyllabusModal: false,
        activeStep: 1,
        openFaq: null,
        toggleFaq(idx) {
            this.openFaq = this.openFaq === idx ? null : idx;
            this.refreshIcons();
        },
        openSyllabus(step) {
            this.activeStep = step || 1;
            this.showSyllabusModal = true;
            this.refreshIcons();
        },
        closeSyllabus() {
            this.showSyllabusModal = false;
        },
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
                    <span>Pusat Sumber Daya</span>
                    <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Edukasi &amp; Kurikulum</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- KIRI: Headline, Value Proposition & Actions (Mobile Center, Desktop Left ~ 5 Cols) -->
                    <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Kicker -->
                            <div class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                KURIKULUM &amp; BASIS PENGETAHUAN UMKM
                            </div>

                            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Kembangkan Usaha Anda dengan Wawasan Finansial &amp; Operasional Nyata
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            Menjalankan bisnis bukan sekadar menunggu pembeli datang. Pelajari disiplin pemisahan arus kas, cara tepat menentukan harga pokok penjualan (HPP), teknik kasir cepat, hingga strategi mengikat pelanggan setia.
                        </p>

                        <!-- Trust Guarantees for UMKM (40-65 y.o. peace of mind) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                            <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Bahasa Sederhana</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Tanpa istilah asing yang membingungkan</div>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                    <i data-lucide="calculator" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Contoh Kasus Riil</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Langsung dari toko, kafe, dan bengkel</div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Primary & Secondary CTA Buttons (Centered on Mobile, Row on Desktop) -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('blog.index') }}"
                                class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-all shadow-sm min-h-[48px]">
                                <span>Buka Katalog 100 Artikel</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                            </a>
                            <button @click="openSyllabus(1)"
                                class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] hover:bg-slate-50 dark:hover:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] text-slate-800 dark:text-slate-200 text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] min-h-[48px]">
                                <i data-lucide="book-open" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF] shrink-0"></i>
                                <span>Lihat Silabus Pembelajaran</span>
                            </button>
                        </div>
                    </div>

                    <!-- KANAN: Product UI Visualization (Real Business Ledger & Recipe Preview - 7 Cols ~ 58%) -->
                    <div class="lg:col-span-7">
                        <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">
                            
                            <!-- Header Window Card -->
                            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Rekapitulasi Usaha Mandiri</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Contoh Laporan Bersih Toko Harian</div>
                                </div>
                                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-[8px]">
                                    Laba Terkunci
                                </span>
                            </div>

                            <!-- Financial Numbers Snapshot -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Penjualan Kotor</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white tabular-nums mt-0.5">Rp 1.450.000</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">42 Transaksi Kasir</div>
                                </div>
                                <div class="p-3 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Modal Bahan Baku (HPP)</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white tabular-nums mt-0.5">Rp 780.000</div>
                                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5">Margin 46.2%</div>
                                </div>
                            </div>

                            <!-- Interactive Topic Highlights -->
                            <div class="space-y-2.5 pt-1">
                                <div class="p-3 rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] bg-[#F9F9FB] dark:bg-[#242426] flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold text-xs shrink-0">
                                            01
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Pemisahan Rekening Usaha</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Cara agar modal kulakan tidak terpakai</div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                </div>

                                <div class="p-3 rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] bg-[#F9F9FB] dark:bg-[#242426] flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            02
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Rumus Menghitung HPP</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Bahan baku, porsi, dan biaya operasional</div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                </div>

                                <div class="p-3 rounded-[14px] border border-black/[0.06] dark:border-white/[0.08] bg-[#F9F9FB] dark:bg-[#242426] flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            03
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Otomasi Kasir POS Kilat</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Cetak struk thermal &amp; nota WhatsApp</div>
                                        </div>
                                    </div>
                                    <i data-lucide="check-circle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
                                </div>
                            </div>

                            <!-- Footer Reassurance -->
                            <div class="p-2.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-xs font-semibold text-center flex items-center justify-center gap-2">
                                <i data-lucide="book-marked" class="w-4 h-4"></i>
                                <span>Materi Edukasi Terbuka Gratis Selamanya</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. JALUR BELAJAR 4 TAHAP (Learning Path / Roadmap) ═══════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section id="kurikulum-bisnis" class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-10">
            <div class="max-w-2xl space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    TAHAPAN BELAJAR BISNIS
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    4 Langkah Kematangan Bisnis dari Gerai Mandiri ke Multi-Cabang
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    Pembelajaran disusun secara realistis berdasarkan tahapan pertumbuhan usaha. Anda dapat langsung mempraktikkannya hari ini juga.
                </p>
            </div>

            <!-- Bento Asymmetric 4-Step Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Tahap 1: Arus Kas -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center font-mono">01</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Fondasi Kas</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Disiplin Arus Kas &amp; Laci Toko</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pemisahan rekening pribadi dari saldo toko, pencatatan pengeluaran harian, serta penyeragaman nota kasir agar tidak ada uang terselip.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button @click="openSyllabus(1)" class="w-full text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-between hover:underline">
                            <span>Pelajari Tahap Ini</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Tahap 2: HPP & Resep -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center font-mono">02</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Presisi Biaya</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Kendali HPP &amp; Stok Resep</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Kalkulasi modal bahan baku per porsi (Bill of Materials), batas minimum peringatan stok menipis, dan kurangi bahan basi.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button @click="openSyllabus(2)" class="w-full text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-between hover:underline">
                            <span>Pelajari Tahap Ini</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Tahap 3: Otomasi Pelanggan -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center font-mono">03</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Efisiensi Tim</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Otomasi WhatsApp &amp; Loyalitas</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Nota digital instan via WhatsApp, penagihan kasbon pelanggan secara tertib, dan delegasi kasir tanpa cemas manipulasi nota.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button @click="openSyllabus(3)" class="w-full text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center justify-between hover:underline">
                            <span>Pelajari Tahap Ini</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Tahap 4: Multi-Cabang -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-[10px] bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-xs flex items-center justify-center font-mono">04</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ekspansi Aman</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">Multi-Cabang &amp; Delegasi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Membuka cabang baru tanpa kehilangan kontrol, perbandingan omzet antar outlet dari satu dashboard HP, dan transfer stok gudang.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button @click="openSyllabus(4)" class="w-full text-xs font-semibold text-purple-600 dark:text-purple-400 flex items-center justify-between hover:underline">
                            <span>Pelajari Tahap Ini</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. EMPAT PILAR PENGETAHUAN & TOOLS (Knowledge Pillars Bento) ════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="border-y border-black/[0.06] dark:border-white/[0.08] py-16 sm:py-20 bg-white/50 dark:bg-[#151B2B]/40">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="max-w-2xl space-y-2">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        PILAR PENGETAHUAN UTAMA
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Koleksi Panduan Terstruktur Sesuai Kebutuhan Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                        Pilih topik bahasan operasional yang sedang Anda hadapi untuk langsung membuka panduan dan alat bantu hitungnya.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Pilar 1: Finansial & Laba Rugi -->
                    <div class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="w-11 h-11 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Manajemen Finansial &amp; Laporan Laba Rugi</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                Pahami cara membaca laporan laba rugi sederhana, memisahkan biaya operasional tetap dan variabel, serta menjaga modal kerja aman dari piutang macet pelanggan.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <a href="{{ route('kalkulator.laba-bersih') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5 hover:underline">
                                <span>Coba Hitung Laba Bersih di Kalkulator</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 2: HPP & BOM -->
                    <div class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                <i data-lucide="boxes" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Penetapan Harga Jual &amp; Resep Bahan (BOM)</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                Hindari jualan laris tapi nombok. Pelajari cara memasukkan takaran gram dan mililiter ke dalam rumus modal pokok, serta perbedaan mendasar antara markup dan margin laba.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <a href="{{ route('kalkulator.hpp') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5 hover:underline">
                                <span>Gunakan Kalkulator HPP Otomatis</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 3: Kasir POS & Printer -->
                    <div class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="w-11 h-11 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Operasional Kasir Kilat &amp; Printer Thermal</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                Cara mudah menyambungkan printer struk Bluetooth mini 58mm/80mm, tips mempercepat antrean saat jam sibuk, dan prosedur tutup shift kasir tanpa selisih uang tunai.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <a href="{{ route('public.resources.guides') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5 hover:underline">
                                <span>Buka Panduan Setup Kasir &amp; Printer</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Pilar 4: WhatsApp & Hubungan Pelanggan -->
                    <div class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="w-11 h-11 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i data-lucide="message-circle" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">Otomasi WhatsApp &amp; Loyalitas Pelanggan</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                Manfaatkan pengiriman nota digital via WhatsApp, ucapan terima kasih otomatis, serta pengingat tagihan piutang pelanggan lama tanpa harus menagih secara canggung.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                            <a href="{{ route('public.omnichannel.whatsapp') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1.5 hover:underline">
                                <span>Lihat Fitur WhatsApp Bisnis COOCA</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. INTEGRASI TOOLS & ARSIP BLOG (Direct Action Bridge) ═══════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-8">
            <div class="p-8 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-6">
                <div class="max-w-2xl space-y-2">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        ALAT BANTU LANGSUNG
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Koleksi Kalkulator &amp; Template Gratis untuk Toko Anda
                    </h3>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                        Selain artikel edukasi, kami menyediakan alat hitung instan dan file spreadsheet siap pakai tanpa perlu mendaftar akun terlebih dahulu.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ route('blog.index') }}" class="p-4 rounded-[16px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] transition-all flex items-center justify-between gap-3 group">
                        <div class="space-y-1 min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition truncate">
                                Katalog 100 Artikel
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Arsip panduan lengkap UMKM</div>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] shrink-0"></i>
                    </a>

                    <a href="{{ route('kalkulator.index') }}" class="p-4 rounded-[16px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] transition-all flex items-center justify-between gap-3 group">
                        <div class="space-y-1 min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition truncate">
                                9 Kalkulator Bisnis
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">HPP, BEP, Gaji, PPh Final</div>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] shrink-0"></i>
                    </a>

                    <a href="{{ route('template.index') }}" class="p-4 rounded-[16px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] transition-all flex items-center justify-between gap-3 group">
                        <div class="space-y-1 min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition truncate">
                                Template Excel Toko
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Download gratis format .xlsx</div>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] shrink-0"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. FAQ EDUKASI BISNIS UMKM ═══════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24 space-y-6">
            <div class="text-center space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    TANYA JAWAB EDUKASI
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Pertanyaan Umum Seputar Pengelolaan Bisnis
                </h2>
            </div>

            <div class="space-y-3 pt-2">
                <!-- Item 1 -->
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(1)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Mengapa pemilik UMKM wajib memisahkan uang pribadi dan uang toko?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Mencampurkan uang pribadi dan uang usaha adalah alasan nomor satu mengapa toko merasa omzetnya besar namun tidak memiliki kas saat hendak kulakan barang. Dengan memisahkan rekening dan menetapkan gaji teratur untuk diri sendiri, modal usaha tetap terlindungi.
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(2)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Bagaimana cara paling mudah menghitung HPP bagi pemula?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Jumlahkan seluruh biaya bahan baku per satu porsi/satuan produk, lalu tambahkan alokasi upah tenaga kerja dan kemasan. Anda dapat menggunakan <a href="{{ route('kalkulator.hpp') }}" class="text-[#007AFF] dark:text-[#0A84FF] font-semibold underline">Kalkulator HPP COOCA</a> untuk menghitungnya secara instan.
                    </div>
                </div>

                <!-- Item 3 -->
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(3)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Apakah seluruh materi dan panduan di COOCA dipungut biaya?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Seluruh artikel edukasi, kalkulator perhitungan bisnis, dan template Excel di ekosistem COOCA dapat diakses secara gratis oleh seluruh pengusaha mandiri di Indonesia tanpa ikatan biaya apa pun.
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. CONVERSION CTA SECTION ════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
            <div class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-center space-y-6 shadow-sm">
                <div class="max-w-2xl mx-auto space-y-3">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        PRAKTIKKAN LANGSUNG
                    </div>
                    <h3 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Siap Mempermudah Operasional Toko Hari Ini?
                    </h3>
                    <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Terapkan kasir kilat, pemotongan stok otomatis saat penjualan, dan pembukuan jernih tanpa biaya pendaftaran awal.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-2">
                    <a href="{{ route('register') }}"
                        class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 active:scale-[0.98] transition-all shadow-sm">
                        <span>Mulai Coba COOCA Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.resources.case-studies') }}"
                        class="h-12 px-7 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] text-slate-900 dark:text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <i data-lucide="award" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                        <span>Baca Kisah Sukses UMKM</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 7. MODAL SHEET: Silabus Lengkap Kurikulum Bisnis ═════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <div x-show="showSyllabusModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-opacity"
                @click="closeSyllabus()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-8 space-y-6">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div>
                            <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">SILABUS EDUKASI</div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-title">
                                Kurikulum Lengkap Operasional Toko
                            </h3>
                        </div>
                        <button @click="closeSyllabus()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#2C2C2E] text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Step Selector Buttons -->
                    <div class="grid grid-cols-4 gap-2">
                        <button @click="activeStep = 1" :class="activeStep === 1 ? 'bg-[#007AFF] text-white font-bold' : 'bg-[#F2F2F7] dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-400 font-medium'" class="py-2 px-3 rounded-[10px] text-xs transition">
                            Tahap 01
                        </button>
                        <button @click="activeStep = 2" :class="activeStep === 2 ? 'bg-[#007AFF] text-white font-bold' : 'bg-[#F2F2F7] dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-400 font-medium'" class="py-2 px-3 rounded-[10px] text-xs transition">
                            Tahap 02
                        </button>
                        <button @click="activeStep = 3" :class="activeStep === 3 ? 'bg-[#007AFF] text-white font-bold' : 'bg-[#F2F2F7] dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-400 font-medium'" class="py-2 px-3 rounded-[10px] text-xs transition">
                            Tahap 03
                        </button>
                        <button @click="activeStep = 4" :class="activeStep === 4 ? 'bg-[#007AFF] text-white font-bold' : 'bg-[#F2F2F7] dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-400 font-medium'" class="py-2 px-3 rounded-[10px] text-xs transition">
                            Tahap 04
                        </button>
                    </div>

                    <!-- Step Details Content -->
                    <div class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div x-show="activeStep === 1" class="space-y-3">
                            <div class="font-bold text-base text-slate-900 dark:text-white">Tahap 01: Disiplin Arus Kas &amp; Laci Toko</div>
                            <p class="text-sm leading-relaxed">Fokus utama tahap ini adalah mengamankan likuiditas harian. Jangan biarkan omzet besar habis untuk kebutuhan konsumtif rumah tangga.</p>
                            <div class="space-y-2 pt-2">
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Membuka 2 rekening terpisah: 1 untuk operasional toko, 1 untuk tabungan pribadi.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Menetapkan nominal modal kembalian awal di laci (float cash) sebelum jam buka toko.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Rekonsiliasi tutup kasir tiap malam: hitung fisik uang kas sebelum cek angka laporan.</span>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeStep === 2" class="space-y-3">
                            <div class="font-bold text-base text-slate-900 dark:text-white">Tahap 02: Kendali HPP &amp; Stok Resep</div>
                            <p class="text-sm leading-relaxed">Menghitung modal bahan secara detail agar harga jual memberikan margin keuntungan yang aman dari fluktuasi harga pasar.</p>
                            <div class="space-y-2 pt-2">
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Menimbang takaran bahan baku per menu/produk menggunakan satuan gram/mililiter.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Menetapkan ambang batas minimum stok (Re-order Point) untuk mencegah kehabisan barang.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Pencatatan tertib barang basi/rusak (waste) agar laporan laba tetap akurat.</span>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeStep === 3" class="space-y-3">
                            <div class="font-bold text-base text-slate-900 dark:text-white">Tahap 03: Otomasi WhatsApp &amp; Loyalitas</div>
                            <p class="text-sm leading-relaxed">Membangun hubungan erat dengan pembeli agar rutin kembali berbelanja tanpa biaya iklan berlebih.</p>
                            <div class="space-y-2 pt-2">
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Pengiriman struk nota digital langsung ke nomor WhatsApp pembeli secara instan.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Pencatatan riwayat piutang kasbon pelanggan dengan batasan pagu kredit otomatis.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Pengingat jatuh tempo pembayaran piutang otomatis dengan kalimat santun.</span>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeStep === 4" class="space-y-3">
                            <div class="font-bold text-base text-slate-900 dark:text-white">Tahap 04: Multi-Cabang &amp; Delegasi</div>
                            <p class="text-sm leading-relaxed">Ekspansi membuka cabang kedua atau ketiga dengan sistem pengawasan sentral yang tidak menyita seluruh waktu tidur Anda.</p>
                            <div class="space-y-2 pt-2">
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Hak akses bertingkat: kasir hanya memproses bayar, laporan keuangan hanya untuk owner.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Surat jalan mutasi stok antar cabang untuk mencegah kebocoran barang di perjalanan.</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span>Laporan konsolidasi omzet seluruh gerai dalam satu layar ringkas di handphone.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('blog.index') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                            Baca Artikel Mendalam &rarr;
                        </a>
                        <button @click="closeSyllabus()" class="h-10 px-5 rounded-[12px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-semibold">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
