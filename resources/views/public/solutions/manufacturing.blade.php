@extends('layouts.public_marketing')

@section('title', 'Software Manufaktur & Produksi UMKM: BOM, Work Order & HPP | COOCA')
@section('description', 'Solusi sistem manufaktur dan pabrikasi UMKM: konveksi, makanan olahan, dan kerajinan.
    Multi-level Bill of Materials (BOM), perintah kerja produksi (SPK), kalkulasi HPP akurat, dan kontrol bahan baku.')
@section('keywords', 'software manufaktur umkm, aplikasi produksi pabrik, sistem kontrol bom hpp, software konveksi
    garmen, aplikasi pabrik makanan, software hpp manufaktur, work order produksi')

    @push('seo')
        <link rel="canonical" href="{{ route('public.solutions.manufacturing') }}">
        <meta property="og:title" content="Software Manufaktur & Produksi UMKM: BOM, Work Order & HPP | COOCA">
        <meta property="og:description"
            content="Kendalikan formula resep BOM bahan baku, jadwalkan perintah produksi, dan hitung HPP produk jadi secara akurat tanpa margin tekor.">
        <meta property="og:url" content="{{ route('public.solutions.manufacturing') }}">
        <meta property="og:type" content="product">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Software Manufaktur & Produksi UMKM: BOM, Work Order & HPP | COOCA">
        <meta name="twitter:description"
            content="Sistem operasi manufaktur UMKM: BOM multi-tingkat, alokasi tenaga kerja, kontrol reject scrap, dan batch tracking siap audit.">

        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Manufacturing Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem manajemen produksi dan manufaktur UMKM untuk kontrol BOM bahan baku, perintah kerja produksi, dan kalkulasi HPP.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- Hero Section (Midnight #060B1E Full-Bleed) --}}
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-slate-400">Solusi Industri</span>
                    <span aria-hidden="true">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Manufaktur & Pabrikasi</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {{-- Left: Narrative & CTA --}}
                    <div class="lg:col-span-7 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-sm">
                            <i data-lucide="factory" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            <span>Sistem Manajemen Produksi & Manufaktur UMKM</span>
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.75rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                            Ubah Bahan Mentah Jadi Produk Jadi <span class="text-[#00C4D8]">dengan HPP Akurat</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl text-pretty">
                            Dirancang untuk konveksi garmen, industri makanan olahan, bengkel fabrikasi kayu/besi, dan
                            manufaktur skala kecil menengah. Hubungkan formula Bill of Materials (BOM), jadwal perintah
                            kerja produksi, upah tenaga kerja, hingga kontrol scrap barang cacat dalam satu sistem terpadu.
                        </p>

                        {{-- Tangible Value Highlights --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-200 min-w-0">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span class="truncate">BOM multi-tingkat & kemasan</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-200 min-w-0">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span class="truncate">Perintah Kerja Produksi (WO) batch</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-200 min-w-0">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span class="truncate">HPP nyata: Bahan + Upah + Overhead</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-slate-200 min-w-0">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span class="truncate">Lacak batch & tanggal kedaluwarsa</span>
                            </div>
                        </div>

                        {{-- CTAs --}}
                        <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ route('register') }}"
                                class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all">
                                <span>Mulai Coba Modul Manufaktur</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Manufaktur%20COOCA"
                                target="_blank" rel="noopener"
                                class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98]">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span>Konsultasi Pabrikasi via WA</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right: Simulated Apple Bento Manufacturing Control Room --}}
                    <div class="lg:col-span-5">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-5 sm:p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-5">
                            <div class="flex items-center justify-between gap-2 border-b border-white/10 pb-4">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse shrink-0"></span>
                                    <span class="text-xs font-mono font-bold text-white truncate">Work Order #WO-PROD-402</span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-0.5 rounded-full shrink-0">
                                    Batch 200 Pcs
                                </span>
                            </div>

                            {{-- Product Target Header --}}
                            <div
                                class="p-3.5 rounded-[14px] bg-[#060B1E] border border-white/10 flex items-center justify-between gap-3 text-xs font-mono">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="shirt" class="w-5 h-5 text-indigo-400 shrink-0" aria-hidden="true"></i>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate">Kemeja Linen Pria Lengan Panjang</p>
                                        <p class="text-[10px] text-slate-400 truncate">Target: 200 Pcs • Jadi: 28 Okt</p>
                                    </div>
                                </div>
                                <span
                                    class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md font-bold shrink-0">On
                                    Schedule</span>
                            </div>

                            {{-- Raw Material BOM Deductions List --}}
                            <div class="space-y-2 text-xs">
                                <span class="text-[11px] font-semibold text-slate-300 block">Alokasi Bahan Mentah
                                    (BOM):</span>

                                <div
                                    class="p-2.5 rounded-[12px] bg-[#060B1E]/60 border border-white/10 flex justify-between items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">Kain Linen Premium 150gsm</p>
                                        <p class="text-[10px] text-slate-300 truncate">320 Meter dari Gudang Utama</p>
                                    </div>
                                    <span class="font-mono text-emerald-400 font-bold shrink-0 text-xs">Rp 9.600.000</span>
                                </div>

                                <div
                                    class="p-2.5 rounded-[12px] bg-[#060B1E]/60 border border-white/10 flex justify-between items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">Kancing Batok & Benang Jahit</p>
                                        <p class="text-[10px] text-slate-300 truncate">1.600 kancing + 10 cone</p>
                                    </div>
                                    <span class="font-mono text-emerald-400 font-bold shrink-0 text-xs">Rp 640.000</span>
                                </div>
                            </div>

                            {{-- HPP Calculation Summary --}}
                            <div class="p-4 rounded-[16px] bg-[#060B1E]/80 border border-white/10 space-y-2 text-xs">
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Total Bahan Mentah (Direct Material)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 10.240.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Ongkos Jahit / Buruh (Direct Labor)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 3.000.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Alokasi Listrik & Kemasan (Overhead)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 760.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-white font-bold pt-2 border-t border-white/10">
                                    <span class="truncate">HPP Pokok Produk Jadi</span>
                                    <span class="font-mono text-sm text-[#00C4D8] shrink-0 font-extrabold">Rp 70.000 / Pcs</span>
                                </div>
                            </div>

                            {{-- QC & Stock In Action --}}
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-[12px] bg-white/5 border border-white/10 flex items-center justify-center gap-2 min-w-0">
                                    <i data-lucide="check-check" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate">QC: 198 Lolos (2 Reject)</span>
                                </div>
                                <div class="p-3 rounded-[12px] bg-white/5 border border-white/10 flex items-center justify-center gap-2 min-w-0">
                                    <i data-lucide="arrow-down-to-dot" class="w-4 h-4 text-[#00C4D8] shrink-0"
                                        aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate">Masuk Gudang Jadi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Content Body with Light/Dark Mode --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-20 sm:space-y-28">

            {{-- Deep Sector Pain Points --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.04] dark:bg-rose-500/[0.08] border border-rose-500/20 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Produksi UMKM
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Titik Rawan Kerugian pada Proses Manufaktur & Fabrikasi
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            01</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">HPP Dihitung Berdasarkan Kira-Kira
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Owner menetapkan harga jual tanpa menghitung persis sisa kain sisa, plastik bungkus, upah
                            borongan, dan biaya listrik mesin, berujung pada omzet besar tetapi kas kosong.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            02</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bahan Mentah Habis di Tengah Jalan
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Produksi terhenti berhari-hari karena persediaan benang, resleting, atau bahan baku utama di
                            gudang ternyata sudah habis tanpa peringatan sistem sejak awal.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            03</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tingkat Reject Tinggi Tanpa Evaluasi
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Barang cacat dan bahan terbuang (scrap) tidak pernah dicatat per operator mesin atau batch
                            produksi, sehingga sumber kebocoran produksi terus terulang tiap bulan.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Kapabilitas Khusus Manufaktur
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                        Fitur Lengkap untuk Mengendalikan Lini Produksi
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Bill of Materials (BOM) Multi-Level
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Definisikan formula kebutuhan bahan mentah, sub-rakitan komponen, bahan penolong, serta label
                            kemasan per 1 unit output barang jadi siap jual.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center">
                            <i data-lucide="clipboard-list" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Surat Perintah Produksi (Work Order)
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Rilis jadwal batch produksi dengan nomor dokumen resmi. Alokasikan bahan mentah dari gudang
                            utama ke lantai produksi dan kunci ketersediaannya.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <i data-lucide="calculator" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Kalkulasi HPP Nyata & Presisi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Gabungkan komponen biaya riil: Harga Pembelian Bahan Mentah (Direct Material), Upah Tenaga Kerja
                            Langsung (Labor), dan Overhead Pabrik per batch pengerjaan.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <i data-lucide="shield-alert" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Inspeksi Quality Control & Scrap
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Catat kuantitas barang lolos QC, produk cacat (reject), dan sisa material yang terbuang. Analisa
                            persentase efisiensi bahan untuk evaluasi lini pabrik.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <i data-lucide="barcode" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Lacak Nomor Batch & Tanggal Expired
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Beri nomor lot/batch pada setiap hasil cetakan produksi. Memudahkan penelusuran jika terjadi
                            komplain konsumen dan siap memenuhi standar sertifikasi BPOM/Halal/SNI.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <i data-lucide="package-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Otomatisasi Stok Produk Jadi (FG)
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Saat Work Order dinyatakan selesai, produk jadi otomatis masuk ke inventaris Finished Goods yang
                            langsung dapat dijual lewat POS Kasir atau Marketplace online.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture (Midnight Strip) --}}
            <section
                class="p-6 sm:p-10 rounded-[24px] bg-[#060B1E] text-white border border-white/10 space-y-8 relative overflow-hidden shadow-xl">
                <div
                    class="absolute -right-20 -bottom-20 w-80 h-80 bg-[#007AFF]/10 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl relative z-10">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#00C4D8] block">
                        Alur Ekosistem Manufaktur
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-white mt-1 leading-snug text-balance break-words">
                        Siklus Dari Pengadaan Bahan Mentah Hingga Distribusi Barang Jadi
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 relative z-10">
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-[#00C4D8]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-white">Pengadaan Raw Material</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">PO bahan mentah diterbitkan ke supplier dan masuk
                            gudang material.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-indigo-400">Langkah 02</div>
                        <h4 class="text-sm font-bold text-white">Penerbitan Work Order</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">BOM formula ditarik dan bahan baku ditransfer ke
                            lini produksi.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-amber-400">Langkah 03</div>
                        <h4 class="text-sm font-bold text-white">Eksekusi & Inspeksi QC</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Barang diproses, diinspeksi, dan HPP final
                            terhitung otomatis.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-emerald-400">Langkah 04</div>
                        <h4 class="text-sm font-bold text-white">Siap Jual & Sinkron ERP</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Produk jadi siap dijual di seluruh channel
                            omnichannel bisnis.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Pertanyaan Umum Seputar COOCA
                        Manufaktur</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA bisa mencatat pekerjaan maklon / CMT ke pihak ketiga?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Bisa. Anda dapat membuat Surat Jalan Pengiriman Bahan Mentah ke mitra CMT/penjahit luar,
                            mencatat ongkos jasa jahit maklon per potong, dan menerima kembali produk setengah jadi atau
                            barang jadi ke gudang Anda secara terdata.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana jika terjadi perbedaan pemakaian bahan nyata vs takaran BOM?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            COOCA memiliki fitur Real Material Consumption Adjustment. Operator dapat menginput jumlah riil
                            bahan yang terpakai jika terjadi penyusutan atau pemborosan di lini produksi. Sistem akan
                            menghitung varians biaya (cost variance) secara transparan.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA cocok untuk usaha makanan olahan dengan expired date?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Sangat cocok. Anda dapat mengaktifkan fitur Batch & Expiry Date Tracking. Setiap kali produk
                            jadi selesai dikemas, sistem mencetak label nomor batch dan tanggal kadaluwarsa, serta
                            menerapkan metode rotasi stok FEFO (First Expired First Out).
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana produk jadi terhubung ke modul penjualan?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Begitu Work Order berstatus 'Completed', persediaan barang jadi langsung tersinkronisasi ke
                            katalog POS Kasir toko offline dan stok marketplace online tanpa perlu input ulang manual.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-slate-200/80 dark:border-white/10 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.inventory') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul Stok</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Gudang Bahan Mentah</h4>
                    </a>
                    <a href="{{ route('public.erp.accounting') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul Akuntansi</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Jurnal HPP Manufaktur</h4>
                    </a>
                    <a href="{{ route('public.solutions.retail') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Retail & Toko</h4>
                    </a>
                    <a href="{{ route('public.solutions.workshop') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Bengkel & Servis</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA (Midnight #060B1E Card) --}}
            <section
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center space-y-6">
                <div
                    class="absolute -right-20 -top-20 w-80 h-80 bg-[#007AFF]/15 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -left-20 -bottom-20 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-4 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400" aria-hidden="true"></i>
                        <span>Tingkatkan Efisiensi Produksi & Margin Laba Pabrik Anda</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-snug text-balance break-words">
                        Kendalikan Biaya Produksi Anda dengan Sistem Terpadu
                    </h3>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed text-pretty">
                        Daftar akun COOCA hari ini dan bangun formula Bill of Materials, pantau perintah kerja produksi,
                        serta hitung HPP riil tanpa ribet spreadsheet.
                    </p>
                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Coba Manufaktur Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                            <span>Lihat Paket Harga</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
