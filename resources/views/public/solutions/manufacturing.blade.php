@extends('layouts.public_marketing')

@section('title', 'Software Manufaktur & Produksi UMKM: BOM, Work Order & HPP | COOCA')
@section('description', 'Solusi sistem manufaktur dan pabrikasi UMKM: konveksi, makanan olahan, dan kerajinan. Multi-level Bill of Materials (BOM), perintah kerja produksi (SPK), kalkulasi HPP akurat, dan kontrol bahan baku.')
@section('og_title', 'Software Manufaktur & Produksi UMKM: BOM, Work Order & HPP | COOCA')
@section('og_description', 'Kendalikan formula resep BOM bahan baku, jadwalkan perintah produksi, dan hitung HPP produk jadi secara akurat tanpa margin tekor.')
@section('canonical', route('public.solutions.manufacturing'))
@section('og_type', 'product')
@section('keywords', 'software manufaktur umkm, aplikasi produksi pabrik, sistem kontrol bom hpp, software konveksi garmen, aplikasi pabrik makanan, software hpp manufaktur, work order produksi')

    @push('seo')
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

        {{-- 1. HERO SECTION (Unified Bento Cockpit - No Breadcrumb) --}}
        <section
            class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Dual Ambient Glowing Blurs --}}
            <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none">
            </div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none">
            </div>

            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-8 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    {{-- Left Column: Narrative & CTA (6 Cols) --}}
                    <div class="lg:col-span-6 space-y-5 text-left">
                        {{-- Typographic Overline Kicker with Pulse Dot (Zero Pill Abuse) --}}
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Sistem Manajemen Produksi &amp; Manufaktur UMKM
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Ubah Bahan Mentah Jadi Produk Jadi <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">dengan HPP Akurat</span>
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Dirancang untuk konveksi garmen, industri makanan olahan, bengkel fabrikasi kayu/besi, dan manufaktur skala kecil menengah. Hubungkan formula Bill of Materials (BOM), jadwal perintah kerja produksi, upah tenaga kerja, hingga kontrol scrap barang cacat dalam satu sistem terpadu.
                        </p>

                        {{-- Tangible Value Highlights Bento Tiles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-left w-full">
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="layers" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">BOM multi-tingkat &amp; kemasan</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="file-check" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Perintah Kerja Produksi (WO) batch</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="calculator" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">HPP riil: Bahan + Upah + Overhead</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="barcode" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Lacak batch &amp; tanggal kedaluwarsa</span>
                            </div>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba Modul Manufaktur</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Manufaktur%20COOCA"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span>Konsultasi Pabrikasi via WA</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Formula BOM Bertingkat</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Kalkulasi HPP Riil per Batch</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Perintah Kerja (WO) Real-Time</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Apple Bento Manufacturing Control Cockpit (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-indigo-500/25 to-[#00C4D8]/25 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-3.5 sm:p-5 lg:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            {{-- Header Table Status --}}
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-500/30">
                                        <i data-lucide="factory" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">Work Order #WO-PROD-402</div>
                                        <div class="text-[10px] text-slate-400 truncate">Lini Produksi Garmen • Batch 200 Pcs</div>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-1 rounded-full shrink-0">
                                    Batch 200 Pcs
                                </span>
                            </div>

                            {{-- Product Target Header --}}
                            <div
                                class="p-3 sm:p-3.5 rounded-xl bg-[#060B1E] border border-white/10 flex items-center justify-between gap-3 text-xs font-mono">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="shirt" class="w-4 h-4 text-indigo-400 shrink-0" aria-hidden="true"></i>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate text-xs">Kemeja Linen Pria Lengan Panjang</p>
                                        <p class="text-[10px] text-slate-400 truncate">Target: 200 Pcs • Deadline: 28 Okt</p>
                                    </div>
                                </div>
                                <span
                                    class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2.5 py-1 rounded-md font-bold shrink-0">On Schedule</span>
                            </div>

                            {{-- Raw Material BOM Deductions List --}}
                            <div class="space-y-2 text-xs">
                                <span class="text-[11px] font-semibold text-slate-300 block">Alokasi Bahan Mentah (BOM):</span>

                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 flex justify-between items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">Kain Linen Premium 150gsm</p>
                                        <p class="text-[10px] text-slate-300 truncate">320 Meter dari Gudang Bahan</p>
                                    </div>
                                    <span class="font-mono text-emerald-400 font-bold shrink-0 text-xs">Rp 9.600.000</span>
                                </div>

                                <div
                                    class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 flex justify-between items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-white text-xs truncate">Kancing Batok &amp; Benang Jahit</p>
                                        <p class="text-[10px] text-slate-300 truncate">1.600 kancing + 10 cone</p>
                                    </div>
                                    <span class="font-mono text-emerald-400 font-bold shrink-0 text-xs">Rp 640.000</span>
                                </div>
                            </div>

                            {{-- HPP Calculation Summary --}}
                            <div class="p-3.5 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-1.5 text-xs">
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Total Bahan Mentah (Direct Material)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 10.240.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Ongkos Jahit / Buruh (Direct Labor)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 3.000.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Alokasi Listrik &amp; Kemasan (Overhead)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 760.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-white font-bold pt-2 border-t border-white/10 text-xs sm:text-sm">
                                    <span class="truncate">HPP Pokok Produk Jadi</span>
                                    <span class="font-mono text-[#00C4D8] shrink-0 font-extrabold">Rp 70.000 / Pcs</span>
                                </div>
                            </div>

                            {{-- QC & Stock In Action --}}
                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2 min-w-0">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate text-[11px]">QC: 198 Lolos (2 Reject)</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2 min-w-0">
                                    <i data-lucide="arrow-down-to-dot" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate text-[11px]">Masuk Gudang Jadi</span>
                                </div>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-indigo-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-400"></i>
                                <span>Multi-Level BOM: <strong class="text-indigo-400">Auto Calculate</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Accurate Real-Cost HPP</span>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
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

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 relative z-10">
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
