@extends('layouts.public_marketing')

@section('title', 'Software ERP Terintegrasi untuk UMKM & Bisnis Berkembang | COOCA')
@section('description', 'COOCA Omnichannel ERP menyatukan kasir POS, stok multi-gudang, purchasing, keuangan, akuntansi,
    CRM, dan karyawan dalam satu sistem terpadu tanpa biaya mahal.')
@section('keywords', 'software erp umkm, omnichannel erp indonesia, sistem erp toko, software manajemen operasional
    terintegrasi, erp kasir gudang akuntansi')

    @push('seo')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "COOCA Omnichannel ERP",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud, Android, iOS",
        "description": "Sistem Enterprise Resource Planning (ERP) modular dan terintegrasi untuk bisnis berkembang dan UMKM Indonesia.",
        "url": "{{ route('public.erp.erp') }}",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR"
        },
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
                "name": "Omnichannel ERP",
                "item": "{{ route('public.erp.erp') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "Core ERP",
                "item": "{{ route('public.erp.erp') }}"
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
                class="absolute bottom-0 left-1/4 w-[420px] h-[420px] bg-[#34C759]/10 rounded-full blur-[130px] pointer-events-none">
            </div>

            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Breadcrumbs -->
                <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-400 pb-6">
                    <a href="{{ route('landing') }}" class="hover:text-[#00C2FF] transition-colors">Beranda</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <span class="text-slate-300">Omnichannel ERP</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/30"></i>
                    <span class="text-white font-semibold">Core ERP</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    <!-- Left: Headline & Core Message (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-[#34C759] tracking-wide">
                            <i data-lucide="box" class="w-3.5 h-3.5"></i>
                            <span>Unified ERP Architecture</span>
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-black text-white tracking-tight leading-[1.2] text-balance break-words">
                            Software ERP Lengkap Tanpa Kerumitan Sistem Korporasi
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl font-normal">
                            Kendalikan rantai pasok, stok barang, kasir toko, pembelian supplier, hingga pembukuan akuntansi
                            dalam satu sistem terintegrasi. Dirancang khusus untuk pemilik usaha berkembang yang ingin
                            sistem rapi tanpa biaya lisensi ratusan juta rupiah.
                        </p>

                        <!-- CTAs -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('register') }}"
                                class="px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Mulai Pakai ERP Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.pos') }}"
                                class="px-6 py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Lihat Fitur Kasir POS</span>
                            </a>
                        </div>

                        <!-- Micro Reassurance -->
                        <div class="pt-2 flex flex-wrap items-center gap-y-2 gap-x-5 text-xs text-slate-400">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Modular: aktifkan modul sesuai kebutuhan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Standar akuntansi Indonesia (SAK EMKM)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                <span>Siap multi-cabang & multi-gudang</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: ERP Matrix Visualization (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div
                            class="bg-[#0B132B]/90 border border-white/10 rounded-[24px] p-5 sm:p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] backdrop-blur-xl space-y-4">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <span class="text-xs font-mono font-bold text-white uppercase">COOCA ERP Central
                                    Matrix</span>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 shrink-0">8
                                    Modules Active</span>
                            </div>

                            <!-- 8 Mini Module Grid -->
                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="monitor" class="w-4 h-4 text-[#00C2FF] shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Kasir POS</div>
                                        <div class="text-[10px] text-slate-400 truncate">Kasir Cepat & Struk</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="boxes" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Inventory</div>
                                        <div class="text-[10px] text-slate-400 truncate">Multi-Gudang & BOM</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="shopping-cart" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Purchasing</div>
                                        <div class="text-[10px] text-slate-400 truncate">PO & Hutang Supplier</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="wallet" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Finance</div>
                                        <div class="text-[10px] text-slate-400 truncate">Arus Kas & Piutang</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Accounting</div>
                                        <div class="text-[10px] text-slate-400 truncate">Jurnal & Laba Rugi</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="users" class="w-4 h-4 text-purple-400 shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">CRM</div>
                                        <div class="text-[10px] text-slate-400 truncate">Database & Retensi</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="user-check" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">HRM Toko</div>
                                        <div class="text-[10px] text-slate-400 truncate">Shift & Absensi Kasir</div>
                                    </div>
                                </div>

                                <div
                                    class="p-3 rounded-[12px] bg-white/[0.04] border border-white/10 flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-rose-400 shrink-0"></i>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Analytics</div>
                                        <div class="text-[10px] text-slate-400 truncate">Dasbor Eksekutif</div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="pt-2 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                                <span class="truncate">Saling terhubung dalam 1 database</span>
                                <span class="text-[#00C2FF] font-semibold shrink-0">Zero Integration Setup</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. KENAPA ERP TRADISIONAL TIDAK COCOK UNTUK UMKM ════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-3xl space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#FF3B30]">Masalah ERP Konvensional</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Mengapa Kebanyakan Software ERP Gagal Diterapkan di Bisnis Berkembang?
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Banyak pengusaha mencoba beralih ke software ERP korporat, namun berujung proyek mangkrak dan uang
                        terbuang sia-sia karena tiga kendala utama.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-red-500/10 text-red-500 flex items-center justify-center font-bold">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">Biaya Awal Selangit</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            ERP tradisional mematok biaya implementasi puluhan hingga ratusan juta rupiah di muka, ditambah
                            biaya konsultan per jam dan biaya lisensi per user yang sangat membebani cash flow bisnis UMKM.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">Terlalu Rumit untuk Staf Toko</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Formulir puluhan kolom dan navigasi kaku membuat kasir dan staf gudang frustrasi. Akhirnya,
                            karyawan kembali mencatat di kertas atau Excel karena sistem baru terlalu lambat dan
                            membingungkan.
                        </p>
                    </div>

                    <div
                        class="p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center font-bold">
                            <i data-lucide="hourglass" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold">Implementasi Berbulan-bulan</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Proses setup memakan waktu 3-6 bulan hanya untuk kustomisasi modul. Bisnis Anda membutuhkan
                            solusi yang langsung siap dipakai dalam hitungan menit tanpa menghentikan operasional toko yang
                            sedang berjalan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. INTEGRASI LINTAS FUNGSI: POS KE AKUNTANSI ═════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 bg-white dark:bg-[#0C101B] border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="max-w-2xl space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Traceability
                        Utuh</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Satu Siklus Bisnis Tertutup (Closed-Loop Workflow)
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Bagaimana alur pengadaan barang supplier terhubung mulus hingga kasir dan laporan keuangan akhir
                        bulan.
                    </p>
                </div>

                <!-- Bento 4-Column Horizontal Step Chain -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-[#007AFF]">01 &bull; PURCHASING</span>
                        <h3 class="text-base font-bold">Order Pembelian Supplier</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Buat Purchase Order (PO) resmi ke supplier. Saat barang tiba, sistem memverifikasi kesesuaian
                            faktur dan mencatat hutang usaha (AP).
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-[#34C759]">02 &bull; GUDANG & STOK</span>
                        <h3 class="text-base font-bold">Penerimaan & Kartu Stok</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Barang masuk otomatis menambah kuantitas di gudang yang ditentukan. Nilai HPP modal rata-rata
                            tertimbang (FIFO/Average) terhitung presisi.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-[#FF9500]">03 &bull; KASIR POS</span>
                        <h3 class="text-base font-bold">Penjualan di Kasir Toko</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Barang dijual lewat kasir POS. Stok langsung terpotong, struk tercetak, dan uang kas masuk
                            diverifikasi sesuai metode pembayaran.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-[#F5F5F7] dark:bg-[#161B26] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <span class="text-xs font-mono font-bold text-purple-500">04 &bull; KEUANGAN & LABA</span>
                        <h3 class="text-base font-bold">Laporan Laba Rugi Terbit</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pendapatan, biaya HPP, dan margin laba bersih terakumulasi di buku besar akuntansi dan tersaji
                            di dasbor keuangan owner.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ SEPUTAR OMNICHANNEL ERP ═══════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 sm:py-24 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Tanya
                        Jawab</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                        Pertanyaan Seputar COOCA Omnichannel ERP
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Ketahui kapabilitas dan batasan sistem agar sesuai dengan kebutuhan bisnis Anda.
                    </p>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah COOCA ERP bisa digunakan jika saya punya beberapa cabang toko fisik?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 1" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Bisa. COOCA mendukung pengelolaan multi-outlet dan multi-gudang. Anda dapat memantau stok tiap
                            cabang, melakukan transfer stok antar cabang dengan surat jalan resmi, dan melihat laporan laba
                            rugi konsolidasi maupun per cabang secara terpisah.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah staf saya dibatasi aksesnya agar tidak melihat data rahasia?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 2" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Ya. COOCA dilengkapi sistem Role-Based Access Control (RBAC). Staf kasir hanya bisa mengakses
                            terminal kasir untuk menjual produk. Staf gudang hanya bisa melihat mutasi stok. Laporan
                            keuangan, margin laba, dan data sensitif lainnya hanya dapat dibuka oleh akun bertingkat Manajer
                            atau Owner.
                        </div>
                    </div>

                    <div
                        class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base flex items-center justify-between gap-4 focus:outline-none">
                            <span>Apakah laporan pembukuan di COOCA sudah sesuai standar akuntansi?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-[#007AFF]"
                                :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openFaq === 3" x-cloak
                            class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed border-t border-black/[0.04] dark:border-white/[0.06] pt-3">
                            Ya. COOCA mengadopsi standar Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK
                            EMKM) dengan bagan akun standar (Chart of Accounts), jurnal umum ganda, buku besar, neraca
                            saldo, dan laporan laba rugi yang siap dicetak untuk keperluan pajak atau pengajuan modal bank.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. RELATED ERP MODULES ═══════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Modul
                            Terkait</span>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight">Pelajari Modul Inti di Dalam ERP COOCA
                        </h3>
                    </div>
                    <a href="{{ route('public.pricing') }}"
                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline">
                        <span>Lihat Harga Paket ERP</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <a href="{{ route('public.erp.pos') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="monitor" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#007AFF] transition-colors">Point of Sale (POS)
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Kasir kilat, cetak
                            struk Bluetooth, barcode scan, & mutasi kas harian.</p>
                    </a>

                    <a href="{{ route('public.erp.inventory') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#34C759]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="boxes" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#34C759] transition-colors">Software Inventory
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Multi-gudang, kartu
                            stok perpetual, resep BOM, & reorder point.</p>
                    </a>

                    <a href="{{ route('public.erp.finance') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-[#FF9500]/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-[#FF9500] transition-colors">Manajemen Finance
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Catat arus kas
                            masuk-keluar, piutang, dan hutang operasional.</p>
                    </a>

                    <a href="{{ route('public.erp.accounting') }}"
                        class="group p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] hover:border-emerald-500/40 hover:shadow-md transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                        </div>
                        <div class="text-base font-bold group-hover:text-emerald-500 transition-colors">Pembukuan Akuntansi
                        </div>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1 leading-snug">Buku besar, neraca
                            saldo, dan laporan laba rugi otomatis.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 6. BOTTOM CTA ════════════════════════════════════════════════════════ -->
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
                            <span class="text-xs font-bold uppercase tracking-wider text-[#00C2FF]">Transformasi
                                Operasional</span>
                            <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                                Waktunya Mengelola Bisnis dengan ERP yang Ringan dan Handal
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 max-w-xl font-normal leading-relaxed">
                                Mulai gratis tanpa kartu kredit. Aktifkan modul kasir dan persediaan toko Anda hari ini dan
                                rasakan efisiensi operasional tanpa batas.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                            <a href="{{ route('register') }}"
                                class="w-full py-4 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-[0_4px_16px_rgba(0,122,255,0.35)] active:scale-[0.98] transition-all min-h-[48px]">
                                <span>Daftar Akun ERP Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('public.demo') }}"
                                class="w-full py-3.5 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 transition-all min-h-[44px]">
                                <span>Coba Demo Interaktif</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
