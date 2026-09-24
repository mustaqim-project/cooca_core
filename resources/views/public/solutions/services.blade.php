@extends('layouts.public_marketing')

@section('title', 'Software Bisnis Jasa & Servis: Booking Jadwal, Teknisi & Invoice | COOCA')
@section('description', 'Solusi aplikasi bisnis jasa, barbershop, klinik, salon, dan servis AC panggilan. Kalender booking janji temu, penugasan teknisi/staf, invoice DP & pelunasan bertahap, dan pengingat WhatsApp anti no-show.')
@section('og_title', 'Software Bisnis Jasa & Servis: Booking Jadwal, Teknisi & Invoice | COOCA')
@section('og_description', 'Atur jadwal booking klien tanpa bentrok, distribusikan pekerjaan teknisi, terbitkan invoice DP dan pelunasan bertahap, serta hitung komisi tim transparan.')
@section('canonical', route('public.solutions.services'))
@section('og_type', 'product')
@section('keywords', 'software bisnis jasa, aplikasi manajemen booking servis, sistem invoicing jasa, software barbershop salon, aplikasi servis ac panggilan, jadwal teknisi lapangan, komisi terapis staf')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Services Operating System",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Sistem manajemen operasional bisnis jasa dan servis untuk kontrol booking waktu, penugasan staf, invoice bertahap, dan komisi.",
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
                                Sistem Manajemen Bisnis Jasa &amp; Servis
                            </p>
                        </div>

                        {{-- Main Headline --}}
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.12] text-balance">
                            Atur Booking Klien, Penugasan Staf, &amp; <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Invoice Termin Tanpa Bentrok</span>
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Kendalikan operasional barbershop, salon kecantikan, klinik, servis AC panggilan, konsultan, hingga studio foto. Hubungkan kalender reservasi janji temu, alokasi teknisi/terapis, penagihan uang muka (DP) dan pelunasan, serta reminder WhatsApp otomatis.
                        </p>

                        {{-- Tangible Value Highlights Bento Tiles --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-left w-full">
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Kalender reservasi anti bentrok jadwal</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="users" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Distribusi tugas staf &amp; teknisi</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Invoice termin: DP &amp; Pelunasan</span>
                            </div>
                            <div class="p-2.5 sm:p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200 leading-snug min-w-0 flex-1">Pengingat janji temu WhatsApp</span>
                            </div>
                        </div>

                        {{-- Action CTAs (Left-aligned) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                            <a href="{{ route('register') }}"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 hover:shadow-xl hover:shadow-[#007AFF]/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 min-h-[48px]">
                                <span>Mulai Coba Sistem Jasa</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            </a>
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Bisnis%20Jasa%20COOCA"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm text-sm font-semibold hover:-translate-y-0.5 active:translate-y-0 transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                <span>Tanya Solusi Jasa</span>
                            </a>
                        </div>

                        {{-- Reassurance Checkpoints --}}
                        <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Kalender Booking Anti-Bentrok</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Termin DP &amp; Pelunasan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Bagi Hasil Komisi Teknisi</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Apple Bento Service Dispatch Deck Cockpit (6 Cols) --}}
                    <div class="lg:col-span-6 relative mt-4 lg:mt-0">
                        {{-- Spotlight glow behind window --}}
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-violet-500/25 to-[#00C4D8]/25 rounded-[32px] blur-xl opacity-75"></div>

                        <div
                            class="relative bg-[#0A122C]/90 border border-white/15 rounded-[18px] sm:rounded-[28px] p-3.5 sm:p-5 lg:p-6 shadow-2xl backdrop-blur-2xl text-white space-y-4">
                            {{-- Specular top highlight line --}}
                            <div class="absolute top-0 inset-x-8 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                            {{-- Header Table Status --}}
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-violet-500/20 text-violet-400 flex items-center justify-center shrink-0 border border-violet-500/30">
                                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs sm:text-sm truncate">Order Servis #SRV-7704</div>
                                        <div class="text-[10px] text-slate-400 truncate">Panggilan Home Service • Sudirman</div>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-1 rounded-full shrink-0">
                                    DP 50% Diterima
                                </span>
                            </div>

                            {{-- Appointment Slot Badge --}}
                            <div
                                class="p-3 sm:p-3.5 rounded-xl bg-[#060B1E] border border-white/10 flex items-center justify-between gap-3 text-xs font-mono">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="calendar" class="w-4 h-4 text-violet-400 shrink-0" aria-hidden="true"></i>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate text-xs">Senin, 10:00 - 12:00 WIB</p>
                                        <p class="text-[10px] text-slate-400 truncate">Klien: PT Maju Bersama • Gd. Graha Lt. 4</p>
                                    </div>
                                </div>
                                <span class="text-[10px] bg-white/10 px-2.5 py-1 rounded-md text-slate-300 font-semibold shrink-0">Home Service</span>
                            </div>

                            {{-- Technician & Service Line Items --}}
                            <div class="space-y-2.5 text-xs">
                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1.5">
                                    <div class="flex justify-between items-start gap-2 font-semibold text-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-xs truncate">Maintenance &amp; Cuci AC Inverter (4 Unit)</p>
                                            <p class="text-[11px] text-slate-300 font-normal truncate">Teknisi: Aris Kurniawan (Lead) + 1 Asisten</p>
                                        </div>
                                        <span class="font-mono text-slate-200 shrink-0 text-xs font-bold">Rp 600.000</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-2 text-[11px] text-violet-400 font-mono pt-1 border-t border-dashed border-white/10">
                                        <span class="truncate">Status: Teknisi di Lokasi</span>
                                        <span class="text-[#00C4D8] shrink-0 font-semibold">Komisi: Rp 180.000</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1.5">
                                    <div class="flex justify-between items-start gap-2 font-semibold text-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-xs truncate">Penggantian Kapasitor &amp; Tambah Freon R32</p>
                                            <p class="text-[11px] text-slate-300 font-normal truncate">Material Part Disetujui Klien</p>
                                        </div>
                                        <span class="font-mono text-slate-200 shrink-0 text-xs font-bold">Rp 350.000</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-2 text-[11px] text-emerald-400 font-mono pt-1 border-t border-dashed border-white/10">
                                        <span class="truncate">Garansi Pengerjaan 30 Hari</span>
                                        <span class="text-slate-300 shrink-0">Foto Bukti Terlampir</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Calculation Breakdown --}}
                            <div class="p-3.5 rounded-xl bg-[#060B1E]/90 border border-white/10 space-y-1.5 text-xs">
                                <div class="flex justify-between items-center gap-2 text-slate-300">
                                    <span class="truncate">Total Tagihan Jasa &amp; Material</span>
                                    <span class="font-mono text-slate-200 shrink-0">Rp 950.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-emerald-400">
                                    <span class="truncate">Down Payment (DP) 50% Terbayar</span>
                                    <span class="font-mono shrink-0">- Rp 475.000</span>
                                </div>
                                <div class="flex justify-between items-center gap-2 text-white font-bold pt-2 border-t border-white/10 text-xs sm:text-sm">
                                    <span class="truncate">Sisa Pelunasan Setelah Selesai</span>
                                    <span class="font-mono text-[#00C4D8] shrink-0 font-extrabold">Rp 475.000</span>
                                </div>
                            </div>

                            {{-- Operational Trigger --}}
                            <div class="grid grid-cols-2 gap-2.5 text-xs">
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2 min-w-0">
                                    <i data-lucide="file-check" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate text-[11px]">Invoice Pelunasan</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center gap-2 min-w-0">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-400 shrink-0" aria-hidden="true"></i>
                                    <span class="font-medium text-slate-200 truncate text-[11px]">Kirim Kuitansi WA</span>
                                </div>
                            </div>

                            {{-- Floating Badges --}}
                            <div class="hidden sm:flex absolute -top-3.5 -right-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-violet-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-violet-400"></i>
                                <span>Booking Calendar: <strong class="text-violet-400">Real-Time Sync</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -bottom-3.5 -left-3.5 items-center gap-2 px-3 py-1.5 rounded-xl bg-[#060B1E]/95 border border-emerald-500/40 shadow-xl backdrop-blur-md text-[11px] font-medium text-white">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Split Invoicing: DP &amp; Final</span>
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
                        Tantangan Bisnis Jasa & Servis
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1 leading-snug text-balance break-words">
                        Kendala yang Menghambat Efisiensi dan Kepuasan Klien Layanan Anda
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            01</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Jadwal Bentrok & No-Show Klien</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Mencatat janji temu via chat manual sering membuat dua klien memesan jam yang sama, atau klien
                            lupa datang sehingga slot waktu staf terbuang sia-sia tanpa omzet.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            02</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Penagihan DP & Termin Berceceran</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Proyek jasa sudah selesai dikerjakan tetapi sisa pelunasan belum dibayar karena tidak ada
                            dokumen invoice resmi yang melacak status uang muka vs sisa tagihan.
                        </p>
                    </div>

                    <div
                        class="p-5 rounded-[18px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center justify-center font-mono">
                            03</div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Distribusi Kerja & Komisi Tidak Rata
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Owner tidak memiliki visibilitas kapasitas teknisi/terapis yang sedang sibuk atau luang, serta
                            penghitungan bagi hasil jasa yang rawan menimbulkan kecemburuan internal.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Kapabilitas Khusus Bisnis Jasa
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug text-balance break-words">
                        Fitur Operasional yang Disesuaikan untuk Layanan Servis
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-violet-500/10 text-violet-500 flex items-center justify-center">
                            <i data-lucide="calendar-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Kalender Booking Janji Temu Visual
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Lihat ketersediaan slot waktu harian dan mingguan secara jernih. Klien dapat memilih jam
                            kunjungan atau jadwal kedatangan tim tanpa tumpang tindih waktu.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center">
                            <i data-lucide="user-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Penugasan Staf & Teknisi Lapangan
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Alokasikan order kerja ke teknisi, terapis, fotografer, atau konsultan yang sedang luang. Pantau
                            status penugasan dari 'Menuju Lokasi' hingga 'Selesai'.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Invoicing Termin (DP & Pelunasan)
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Terbitkan nota penagihan bertahap: uang muka (Down Payment) sebelum pengerjaan dimulai dan
                            tagihan pelunasan setelah lembar berita acara selesai disetujui.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <i data-lucide="bell" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pengingat Janji Temu via WhatsApp
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Kirim reminder otomatis H-1 atau H-2 jam sebelum waktu booking ke nomor WhatsApp klien: "Halo Bu
                            Maya, mengingatkan kembali jadwal perawatan wajah besok pukul 13:00 WIB."
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <i data-lucide="clipboard-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Checklist Inspeksi & Bukti Kerja
                            Digital</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Lampirkan foto sebelum dan sesudah pengerjaan (before/after), catatan teknis pekerjaan, serta
                            tanda tangan digital klien langsung dari smartphone staf lapangan.
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-[22px] space-y-3 shadow-sm hover:border-[#007AFF]/40 transition-all">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <i data-lucide="percent" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Komisi & Bagi Hasil Staf Transparan
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Sistem menghitung persentase komisi bagi hasil terapis atau teknisi secara otomatis untuk setiap
                            order servis yang sukses diselesaikan, siap masuk rekap payroll.
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
                        Alur Ekosistem Jasa
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-white mt-1 leading-snug text-balance break-words">
                        Siklus Booking, Pelaksanaan Servis, Hingga Pelunasan Invoice
                    </h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 relative z-10">
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-[#00C4D8]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-white">Reservasi Jadwal</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Slot waktu diamankan dan invoice uang muka (DP)
                            diterbitkan ke klien.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-violet-400">Langkah 02</div>
                        <h4 class="text-sm font-bold text-white">Penugasan Staf</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Teknisi atau staf menerima surat tugas digital
                            dengan rincian kebutuhan.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-emerald-400">Langkah 03</div>
                        <h4 class="text-sm font-bold text-white">Eksekusi & Checklist</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Pekerjaan diselesaikan dengan bukti foto dan
                            verifikasi kepuasan klien.</p>
                    </div>
                    <div class="p-5 rounded-[18px] bg-white/5 border border-white/10 space-y-2 backdrop-blur-sm">
                        <div class="text-xs font-mono font-bold text-amber-400">Langkah 04</div>
                        <h4 class="text-sm font-bold text-white">Pelunasan & Komisi</h4>
                        <p class="text-xs text-slate-300 leading-relaxed text-pretty">Tagihan lunas tercatat di kas dan komisi staf
                            masuk rekap otomatis.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Pertanyaan Umum Seputar COOCA
                        Bisnis Jasa</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA cocok untuk jasa panggilan (home service) maupun studio di tempat?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Ya, sangat fleksibel. Untuk bisnis studio/klinik (seperti salon atau barbershop), sistem
                            mengelola ketersediaan kursi atau ruangan perawatan. Untuk jasa panggilan (seperti servis AC
                            atau pembersihan rumah), sistem mencatat alamat klien dan rute penugasan teknisi.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana jika klien membutuhkan penambahan pekerjaan atau sparepart di lokasi?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Teknisi atau kasir dapat menambahkan item jasa tambahan maupun material suku cadang langsung ke
                            dalam Surat Perintah Kerja aktif dari smartphone. Invoice pelunasan akan otomatis
                            mengakumulasikan biaya baru tersebut secara transparan.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Bagaimana cara mengirim invoice dan kuitansi pembayaran resmi ke klien?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            COOCA menyediakan fitur cetak invoice PDF profesional berlogo perusahaan Anda yang dapat
                            dikirimkan langsung ke email atau WhatsApp klien dalam bentuk tautan digital resmi yang aman.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span>Apakah COOCA mendukung skema kontrak servis berkala (maintenance retainer)?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-white/10 pt-3">
                            Bisa. Anda dapat mengatur jadwal kunjungan berulang (bulanan/triwulanan) untuk klien korporat
                            dengan sistem pengingat otomatis sebelum jadwal servis jatuh tempo.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-slate-200/80 dark:border-white/10 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.crm') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul CRM</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Database & Histori Klien</h4>
                    </a>
                    <a href="{{ route('public.erp.finance') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Modul Keuangan</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Invoicing & Cash Flow</h4>
                    </a>
                    <a href="{{ route('public.solutions.workshop') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Bengkel & Otomotif</h4>
                    </a>
                    <a href="{{ route('public.solutions.laundry') }}"
                        class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-md transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">Solusi Industri</span>
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 group-hover:text-[#007AFF] dark:group-hover:text-[#00C4D8] transition-colors">
                            Laundry & Dry Cleaning</h4>
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
                        <span>Tingkatkan Profesionalitas dan Ketepatan Waktu Layanan Anda</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-snug text-balance break-words">
                        Kelola Reservasi dan Tim Lapangan Anda dari Satu Sistem
                    </h3>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed text-pretty">
                        Daftar akun COOCA hari ini dan nikmati kemudahan menjadwalkan booking klien, menerbitkan invoice
                        termin, serta membagikan komisi staf secara transparan.
                    </p>
                    <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ route('register') }}"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-95 transition-all">
                            <span>Coba Software Jasa Gratis</span>
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
