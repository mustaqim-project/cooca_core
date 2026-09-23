@extends('layouts.public_marketing')

@section('title', 'Software Bisnis Jasa & Servis: Booking Jadwal, Teknisi & Invoice | COOCA')
@section('description', 'Solusi aplikasi bisnis jasa, barbershop, klinik, salon, dan servis AC panggilan. Kalender booking janji temu, penugasan teknisi/staf, invoice DP & pelunasan bertahap, dan pengingat WhatsApp anti no-show.')
@section('keywords', 'software bisnis jasa, aplikasi manajemen booking servis, sistem invoicing jasa, software barbershop salon, aplikasi servis ac panggilan, jadwal teknisi lapangan, komisi terapis staf')

@push('seo')
    <link rel="canonical" href="{{ route('public.solutions.services') }}">
    <meta property="og:title" content="Software Bisnis Jasa & Servis: Booking Jadwal, Teknisi & Invoice | COOCA">
    <meta property="og:description" content="Atur jadwal booking klien tanpa bentrok, distribusikan pekerjaan teknisi, terbitkan invoice DP dan pelunasan bertahap, serta hitung komisi tim transparan.">
    <meta property="og:url" content="{{ route('public.solutions.services') }}">
    <meta property="og:type" content="product">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software Bisnis Jasa & Servis: Booking Jadwal, Teknisi & Invoice | COOCA">
    <meta name="twitter:description" content="Sistem operasi bisnis jasa modern: kalender reservasi, penugasan staf, reminder WhatsApp, dan penagihan proyek profesional.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
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
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span aria-hidden="true">/</span>
                <span>Solusi Industri</span>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Bisnis Jasa & Servis</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Narrative & CTA --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-500/10 dark:bg-violet-400/15 border border-violet-500/20 text-xs font-semibold text-violet-700 dark:text-violet-300">
                        <i data-lucide="briefcase" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Sistem Manajemen Bisnis Jasa & Servis</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Atur Booking Waktu Klien, Penugasan Staf, & Invoice Termin Tanpa Bentrok
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Kendalikan operasional barbershop, salon kecantikan, klinik, servis AC panggilan, konsultan, hingga studio foto. Hubungkan kalender reservasi janji temu, alokasi teknisi/terapis, penagihan uang muka (DP) dan pelunasan, serta reminder WhatsApp otomatis.
                    </p>

                    {{-- Tangible Value Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Kalender reservasi anti bentrok jadwal</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Distribusi tugas staf & teknisi lapangan</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Invoice bertahap: Down Payment (DP) & Pelunasan</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Pengingat janji temu WhatsApp anti no-show</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba Sistem Jasa</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Bisnis%20Jasa%20COOCA" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                            <span>Tanya Solusi Jasa</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Simulated Apple Bento Service Dispatch Deck --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Order Servis #SRV-7704</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 px-2.5 py-0.5 rounded-full">
                                DP 50% Diterima
                            </span>
                        </div>

                        {{-- Appointment Slot Badge --}}
                        <div class="p-3.5 rounded-[14px] bg-neutral-900 text-white flex items-center justify-between text-xs font-mono">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="calendar" class="w-5 h-5 text-violet-400" aria-hidden="true"></i>
                                <div>
                                    <p class="font-bold text-white">Senin, 10:00 - 12:00 WIB</p>
                                    <p class="text-[10px] text-neutral-400">Klien: PT Maju Bersama • Lokasi: Gd. Graha Lt. 4</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-white/10 px-2 py-0.5 rounded text-neutral-300">Home Service</span>
                        </div>

                        {{-- Technician & Service Line Items --}}
                        <div class="space-y-3 text-xs">
                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-1.5">
                                <div class="flex justify-between items-start font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">Maintenance & Cuci AC Inverter (4 Unit)</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Teknisi: Aris Kurniawan (Lead) + 1 Asisten</p>
                                    </div>
                                    <span class="font-mono">Rp 600.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-violet-600 dark:text-violet-400 font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Status: Teknisi Tiba di Lokasi</span>
                                    <span>Komisi Jasa: Rp 180.000</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-1.5">
                                <div class="flex justify-between items-start font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">Penggantian Kapasitor & Tambah Freon R32</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Material Part Tambahan Disetujui Klien</p>
                                    </div>
                                    <span class="font-mono">Rp 350.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Garansi Pengerjaan 30 Hari</span>
                                    <span>Foto Bukti Terlampir</span>
                                </div>
                            </div>
                        </div>

                        {{-- Calculation Breakdown --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50/80 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-800 space-y-2 text-xs">
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Total Tagihan Jasa & Material</span>
                                <span class="font-mono">Rp 950.000</span>
                            </div>
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span>Down Payment (DP) 50% Terbayar</span>
                                <span class="font-mono">- Rp 475.000</span>
                            </div>
                            <div class="flex justify-between text-[#1D1D1F] dark:text-[#F5F5F7] font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <span>Sisa Pelunasan Setelah Selesai</span>
                                <span class="font-mono text-sm text-[#007AFF]">Rp 475.000</span>
                            </div>
                        </div>

                        {{-- Operational Trigger --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="file-check" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Invoice Pelunasan</span>
                            </div>
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="message-square" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Kirim Kuitansi WA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Deep Sector Pain Points --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Bisnis Jasa & Servis
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Kendala yang Menghambat Efisiensi dan Kepuasan Klien Layanan Anda
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">01</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jadwal Bentrok & No-Show Klien</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Mencatat janji temu via chat manual sering membuat dua klien memesan jam yang sama, atau klien lupa datang sehingga slot waktu staf terbuang sia-sia tanpa omzet.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">02</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Penagihan DP & Termin Berceceran</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Proyek jasa sudah selesai dikerjakan tetapi sisa pelunasan belum dibayar karena tidak ada dokumen invoice resmi yang melacak status uang muka vs sisa tagihan.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">03</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Distribusi Kerja & Komisi Tidak Rata</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Owner tidak memiliki visibilitas kapasitas teknisi/terapis yang sedang sibuk atau luang, serta penghitungan bagi hasil jasa yang rawan menimbulkan kecemburuan internal.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Kapabilitas Khusus Bisnis Jasa
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Fitur Operasional yang Disesuaikan untuk Layanan Servis
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-violet-500/10 text-violet-600 flex items-center justify-center">
                            <i data-lucide="calendar-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kalender Booking Janji Temu Visual</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Lihat ketersediaan slot waktu harian dan mingguan secara jernih. Klien dapat memilih jam kunjungan atau jadwal kedatangan tim tanpa tumpang tindih waktu.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="user-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Penugasan Staf & Teknisi Lapangan</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Alokasikan order kerja ke teknisi, terapis, fotografer, atau konsultan yang sedang luang. Pantau status penugasan dari 'Menuju Lokasi' hingga 'Selesai'.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Invoicing Termin (DP & Pelunasan)</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Terbitkan nota penagihan bertahap: uang muka (Down Payment) sebelum pengerjaan dimulai dan tagihan pelunasan setelah lembar berita acara selesai disetujui.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="bell" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pengingat Janji Temu via WhatsApp</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kirim reminder otomatis H-1 atau H-2 jam sebelum waktu booking ke nomor WhatsApp klien: "Halo Bu Maya, mengingatkan kembali jadwal perawatan wajah besok pukul 13:00 WIB."
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="clipboard-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Checklist Inspeksi & Bukti Kerja Digital</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Lampirkan foto sebelum dan sesudah pengerjaan (before/after), catatan teknis pekerjaan, serta tanda tangan digital klien langsung dari smartphone staf lapangan.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="percent" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Komisi & Bagi Hasil Staf Transparan</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Sistem menghitung persentase komisi bagi hasil terapis atau teknisi secara otomatis untuk setiap order servis yang sukses diselesaikan, siap masuk rekap payroll.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Alur Ekosistem Jasa
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Siklus Booking, Pelaksanaan Servis, Hingga Pelunasan Invoice
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Reservasi Jadwal</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Slot waktu diamankan dan invoice uang muka (DP) diterbitkan ke klien.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-violet-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Penugasan Staf</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Teknisi atau staf menerima surat tugas digital dengan rincian kebutuhan.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Eksekusi & Checklist</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pekerjaan diselesaikan dengan bukti foto dan verifikasi kepuasan klien.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-amber-500">Langkah 04</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pelunasan & Komisi</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Tagihan lunas tercatat di kas dan komisi staf masuk rekap otomatis.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Umum Seputar COOCA Bisnis Jasa</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA cocok untuk jasa panggilan (home service) maupun studio di tempat?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Ya, sangat fleksibel. Untuk bisnis studio/klinik (seperti salon atau barbershop), sistem mengelola ketersediaan kursi atau ruangan perawatan. Untuk jasa panggilan (seperti servis AC atau pembersihan rumah), sistem mencatat alamat klien dan rute penugasan teknisi.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana jika klien membutuhkan penambahan pekerjaan atau sparepart di lokasi?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Teknisi atau kasir dapat menambahkan item jasa tambahan maupun material suku cadang langsung ke dalam Surat Perintah Kerja aktif dari smartphone. Invoice pelunasan akan otomatis mengakumulasikan biaya baru tersebut secara transparan.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana cara mengirim invoice dan kuitansi pembayaran resmi ke klien?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA menyediakan fitur cetak invoice PDF profesional berlogo perusahaan Anda yang dapat dikirimkan langsung ke email atau WhatsApp klien dalam bentuk tautan digital resmi yang aman.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA mendukung skema kontrak servis berkala (maintenance retainer)?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Bisa. Anda dapat mengatur jadwal kunjungan berulang (bulanan/triwulanan) untuk klien korporat dengan sistem pengingat otomatis sebelum jadwal servis jatuh tempo.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-neutral-200/80 dark:border-neutral-800 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.crm') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul CRM</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Database & Histori Klien</h4>
                    </a>
                    <a href="{{ route('public.erp.finance') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Keuangan</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Invoicing & Cash Flow</h4>
                    </a>
                    <a href="{{ route('public.solutions.workshop') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Bengkel & Otomotif</h4>
                    </a>
                    <a href="{{ route('public.solutions.laundry') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Laundry & Dry Cleaning</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Tingkatkan Profesionalitas dan Ketepatan Waktu Layanan Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Kelola Reservasi dan Tim Lapangan Anda dari Satu Sistem
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun COOCA hari ini dan nikmati kemudahan menjadwalkan booking klien, menerbitkan invoice termin, serta membagikan komisi staf secara transparan.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Coba Software Jasa Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Lihat Paket Harga</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
