@extends('layouts.public_marketing')

@section('title', 'Software Bengkel Motor & Mobil: SPK, Stok Sparepart & Komisi Mekanik | COOCA')
@section('description', 'Solusi sistem manajemen bengkel motor dan mobil. Surat Perintah Kerja (SPK) digital, lacak histori servis plat nomor, potong stok sparepart & oli otomatis, komisi montir, dan reminder WhatsApp.')
@section('og_title', 'Software Bengkel Motor & Mobil: SPK, Stok Sparepart & Komisi Mekanik | COOCA')
@section('og_description', 'Tinggalkan nota kertas minyak. Catat histori servis plat kendaraan, kelola stok oli & suku cadang, dan hitung komisi montir otomatis.')
@section('canonical', route('public.solutions.workshop'))
@section('og_type', 'product')
@section('keywords', 'software bengkel terintegrasi, aplikasi bengkel motor mobil, sistem manajemen bengkel, software kasir bengkel, surat perintah kerja bengkel spk, komisi mekanik montir, stok sparepart bengkel')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Workshop Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem manajemen operasional bengkel motor dan mobil untuk kontrol SPK, suku cadang, komisi mekanik, dan database kendaraan.",
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
                                Sistem Operasi Bengkel Motor &amp; Mobil
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Tinggalkan Nota Kertas Minyak. <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Kendalikan SPK &amp; Komisi</span>
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Ubah bengkel Anda menjadi lebih profesional. Mulai dari pendaftaran nomor polisi kendaraan, penerbitan Surat Perintah Kerja (SPK), pemotongan stok suku cadang &amp; oli otomatis, hingga hitung bagi hasil jasa teknisi tanpa perdebatan di akhir bulan.
                        </p>

                        {{-- Tangible Value Highlights Bento Tiles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-left w-full">
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="car" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Database plat nomor &amp; riwayat servis</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="wrench" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Stok oli &amp; sparepart real-time</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="calculator" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Kalkulasi komisi montir otomatis</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Pengingat servis berkala WhatsApp</span>
                            </div>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba Sistem Bengkel</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Bengkel%20COOCA"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span>Tanya Solusi Bengkel</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Database Plat &amp; Riwayat Servis</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Potong Stok Rak Otomatis</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Bagi Hasil Teknisi Akurat</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Apple Bento Workshop Service Station (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#007AFF]/30 to-[#00C4D8]/30 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-3.5 sm:p-5 lg:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            {{-- Header WO Status --}}
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-[#00C4D8] flex items-center justify-center shrink-0 border border-blue-500/30">
                                        <i data-lucide="wrench" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">SPK #WO-8821 • Pengerjaan</div>
                                        <div class="text-[10px] text-slate-400 truncate">Pit 02 (Doni) • Servis Berkala</div>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-1 rounded-full shrink-0">
                                    Pit 02 (Doni)
                                </span>
                            </div>

                            {{-- Vehicle Badge --}}
                            <div
                                class="p-3 rounded-xl bg-[#060B1E]/90 border border-white/10 flex items-center justify-between gap-3 text-xs font-mono">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20">
                                        <i data-lucide="car" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white tracking-wider truncate">B 4821 KFA</p>
                                        <p class="text-[10px] text-slate-400 font-sans truncate">Honda Vario 160 • 14.820 KM</p>
                                    </div>
                                </div>
                                <span class="text-[10px] bg-white/10 px-2.5 py-1 rounded-lg text-slate-300 font-sans font-semibold shrink-0">Servis Berkala</span>
                            </div>

                            {{-- Itemized Parts & Services List --}}
                            <div class="space-y-2.5 text-xs">
                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-2">
                                    <div class="flex justify-between items-start gap-2 font-semibold text-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-white text-xs truncate">Oli Mesin Matic Full Synthetic 0.8L</p>
                                            <p class="text-[11px] text-slate-300 font-normal truncate">Suku Cadang (Stok Potong Rak B2)</p>
                                        </div>
                                        <span class="font-mono text-slate-200 shrink-0 text-xs font-bold">Rp 65.000</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-2 text-[11px] text-emerald-400 font-mono pt-1.5 border-t border-dashed border-white/10">
                                        <span class="truncate">HPP Part: Rp 48.000</span>
                                        <span class="text-[#00C4D8] shrink-0 font-semibold">Sisa: 12 Btl</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-2">
                                    <div class="flex justify-between items-start gap-2 font-semibold text-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-white text-xs truncate">Jasa Servis CVT &amp; Ganti Oli</p>
                                            <p class="text-[11px] text-slate-300 font-normal truncate">Biaya Jasa Teknisi</p>
                                        </div>
                                        <span class="font-mono text-slate-200 shrink-0 text-xs font-bold">Rp 55.000</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-2 text-[11px] text-[#00C4D8] font-mono pt-1.5 border-t border-dashed border-white/10">
                                        <span class="truncate">Komisi (40%): Rp 22.000</span>
                                        <span class="text-slate-300 shrink-0">Tercatat ke Doni</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Calculation Breakdown --}}
                            <div class="p-3.5 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-1.5 text-xs">
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Total Sparepart (1 Item)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 65.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Total Ongkos Jasa (1 Item)</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 55.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-white font-bold pt-2 border-t border-white/10 text-xs sm:text-sm">
                                    <span class="truncate">Total Tagihan Pelanggan</span>
                                    <span class="font-mono text-[#00C4D8] shrink-0 font-extrabold">Rp 120.000</span>
                                </div>
                            </div>

                            {{-- Quick Operations Status --}}
                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-[#00C4D8]" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 text-[11px] truncate">Cetak SPK &amp; Nota</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-400" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 text-[11px] truncate">WA Siap Ambil</span>
                                </div>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="car" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Database Kendaraan: <strong class="text-emerald-400">Live</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-[#00C4D8]/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="calculator" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                <span>Komisi Montir: Auto-Calc</span>
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
                        Tantangan Pengelolaan Bengkel
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Masalah Klasik yang Kerap Menimbulkan Kerugian dan Salah Paham
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            01</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Sparepart & Oli Bocor Tanpa Nota</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Mekanik mengambil busi, kampas rem, atau oli dari gudang tetapi lupa dicatat di nota kasir,
                            membuat stok fisik habis saat dibutuhkan pelanggan lain.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            02</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Perselisihan Upah Komisi Montir</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Penghitungan komisi jasa mekanik manual di akhir bulan sering memicu kecurigaan antar montir
                            mengenai siapa yang mengerjakan servis tertentu.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            03</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pelanggan Hilang Setelah Servis</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Tidak adanya database histori nomor polisi membuat bengkel tidak tahu kapan jadwal ganti oli
                            pelanggan berikutnya, kehilangan peluang repeat order bernilai jutaan rupiah.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Kapabilitas Khusus Bengkel
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                        Fitur Spesifik untuk Operasional Bengkel Roda Dua & Empat
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Surat Perintah Kerja (SPK) Digital
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Catat keluhan pemilik kendaraan, kilometer (KM) terkini, dan estimasi biaya perbaikan sebelum
                            montir membongkar mesin, menghindari salah paham saat pembayaran.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <i data-lucide="car" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Histori Kendaraan per Plat Nomor
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Cukup ketik plat nomor (contoh: B 4821 KFA), montir langsung dapat melihat riwayat suku cadang
                            apa saja yang pernah diganti dan tanggal servis terakhir.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <i data-lucide="package-search" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Stok Sparepart & Oli Real-Time</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Sistem secara otomatis mengurangi stok fisik di gudang begitu part dimasukkan ke dalam SPK
                            aktif. Tersedia peringatan stok minimum untuk order ke distributor.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <i data-lucide="users" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Perhitungan Komisi Montir Otomatis
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Bagi hasil upah jasa mekanik (misal: 30% atau 40% dari ongkos pasang) dihitung presisi per SPK
                            yang selesai. Rekap slip gaji montir transparan dan bebas sengketa.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <i data-lucide="bell-ring" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Reminder Servis & Oli via WhatsApp
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Kirim pesan ramah otomatis ke WhatsApp pelanggan 60 hari setelah servis: "Kendaraan B 4821 KFA
                            sudah waktunya ganti oli berkala agar performa mesin tetap prima."
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <i data-lucide="receipt" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Nota Terperinci Jasa & Part</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Struk cetak kasir membedakan dengan jelas rincian harga suku cadang dan ongkos jasa teknisi,
                            lengkap dengan garansi servis untuk meningkatkan kepercayaan pelanggan.
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
                        Alur Ekosistem Bengkel
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-white mt-1 leading-snug text-balance break-words">
                        Bagaimana COOCA Menghubungkan Front Office, Gudang, & Teknisi
                    </h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 relative z-10">
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-[#00C4D8]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-white">Input Plat & Keluhan</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Front desk mencatat KM kendaraan dan mencetak SPK
                            ke mekanik.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-amber-400">Langkah 02</div>
                        <h4 class="text-sm font-bold text-white">Pengambilan Sparepart</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Stok oli & part terpotong langsung dari gudang
                            saat dipasang.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-emerald-400">Langkah 03</div>
                        <h4 class="text-sm font-bold text-white">Selesai & Kasir Cetak</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Pelanggan membayar invoice dan komisi montir
                            otomatis tercatat.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-purple-400">Langkah 04</div>
                        <h4 class="text-sm font-bold text-white">Automated WA CRM</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Sistem menjadwalkan notifikasi WhatsApp servis
                            berkala secara cerdas.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Pertanyaan Umum Seputar COOCA
                        Bengkel</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah satu SPK bisa dikerjakan oleh lebih dari satu mekanik?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Bisa. Anda dapat mengalokasikan mekanik berbeda untuk setiap item pekerjaan jasa. Misalnya,
                            pengerjaan kelistrikan dialokasikan ke Mekanik A, sementara servis suspensi/kaki-kaki
                            dialokasikan ke Mekanik B. Komisi masing-masing montir akan dihitung secara proporsional.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA cocok untuk bengkel motor maupun bengkel mobil?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Ya. Sistem dirancang fleksibel untuk bengkel motor umum, authorized dealer, bengkel mobil,
                            bengkel AC mobil, variasi/aksesoris, hingga spesialis ban (spooring-balancing). Kolom data nomor
                            rangka, nomor mesin, dan kilometer dapat disesuaikan.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana cara mencatat oli curah (drum) yang dijual per liter?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            COOCA mendukung multi-satuan dengan nilai desimal. Anda dapat membeli oli dalam kemasan drum 200
                            liter, lalu menjualnya per 0.8 liter, 1 liter, atau 3.5 liter ke pelanggan saat servis. Stok
                            drum berkurang secara presisi tanpa pembulatan kasar.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana mencetak struk nota servis agar terlihat resmi?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            COOCA mendukung cetak struk thermal 58mm/80mm untuk printer kasir cepat, serta invoice format
                            A4/A5 menggunakan printer biasa lengkap dengan logo bengkel, tanda tangan mekanik, dan ketentuan
                            garansi perbaikan.
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
                            Stok Suku Cadang & Oli</h4>
                    </a>
                    <a href="{{ route('public.erp.pos') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul Kasir</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Kasir Kasbon & QRIS</h4>
                    </a>
                    <a href="{{ route('public.solutions.services') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Bisnis Jasa & Servis</h4>
                    </a>
                    <a href="{{ route('public.solutions.manufacturing') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Manufaktur & Pabrikasi</h4>
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
                        <span>Solusi Teruji untuk Ratusan Bengkel Motor & Mobil Indonesia</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-snug text-balance break-words">
                        Mulai Operasikan Bengkel Anda dengan Standar Modern
                    </h3>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed text-pretty">
                        Daftar akun COOCA hari ini dan rasakan kemudahan mengontrol SPK servis kendaraan, suku cadang, dan
                        upah montir secara otomatis.
                    </p>
                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Coba Software Bengkel Gratis</span>
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
