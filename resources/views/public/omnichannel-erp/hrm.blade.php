@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Karyawan, Shift & Absensi Outlet | COOCA')
@section('description',
    'Aplikasi manajemen staf dan SDM untuk toko ritel, F&B, dan jasa. Atur jadwal shift kerja, presensi digital GPS, pantau produktivitas penjualan kasir, dan rekap penggajian berbasis kehadiran.')
@section('og_title', 'Software Manajemen Karyawan, Shift & Absensi Outlet | COOCA')
@section('og_description',
    'Aplikasi manajemen staf dan SDM untuk toko ritel, F&B, dan jasa. Atur jadwal shift kerja, presensi digital GPS, pantau produktivitas penjualan kasir, dan rekap penggajian berbasis kehadiran.')
@section('keywords',
    'software hrm karyawan outlet, aplikasi jadwal shift kasir, absensi gps toko, manajemen sdm retail, rekap gaji karyawan umkm')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA HRM & Staff Management",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Modul manajemen SDM, absensi, penjadwalan shift, dan pencatatan komisi karyawan cabang.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Penyusunan dan distribusi jadwal shift kerja mingguan/bulanan per cabang",
    "Presensi digital staf berbasis verifikasi lokasi GPS dan PIN kasir",
    "Pencatatan komisi dan produktivitas penjualan individual karyawan",
    "Pengelolaan izin, sakit, cuti tahunan, dan persetujuan tukar shift digital",
    "Rekap jam kerja aktual dan komponen gaji kehadiran untuk modul Finance"
  ]
}
</script>
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Bagaimana cara kerja absensi staf outlet di COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Staf dapat melakukan absensi langsung saat membuka shift di tablet kasir POS menggunakan PIN unik mereka, atau melalui smartphone pribadi dengan verifikasi radius GPS geofencing toko sehingga staf tidak bisa titip absen dari rumah."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah sistem bisa menangani staf yang dipindahkan ke cabang lain?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mengatur penugasan staf ke cabang tertentu secara permanen maupun rotasi harian/mingguan. Saat staf bertugas di Cabang B, hak akses kasir dan presensi mereka otomatis berlaku di cabang tersebut."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah COOCA bisa menghitung komisi penjualan per kasir atau teknisi?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. Setiap transaksi kasir atau pengerjaan order jasa tercatat atas nama staf yang bersangkutan. Anda dapat mengatur skema komisi persentase atau nominal tetap per item/layanan yang terjual."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana data kehadiran ini terhubung ke pembukuan gaji?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Rekap kehadiran (total shift masuk, keterlambatan, dan lembur) otomatis menghasilkan ringkasan gaji bulanan yang dapat langsung disetujui owner dan dibukukan sebagai beban gaji operasional di modul Akuntansi & Finance."
      }
    }
  ]
}
</script>
    @endpush

@section('content')
    <div
        class="relative overflow-hidden bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Full Viewport 50/50 Ratio - Apple HIG Cockpit) ══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">

            <!-- Subtle Ambient Background Glows (Pure CSS, No Heavy Images) -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Safe from Fixed Bottom Nav on Mobile) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-3 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-6 sm:pb-24 lg:py-14">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-6 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile and Desktop ~ 6 Cols) -->
                    <div
                        class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Breadcrumb & Overline Kicker -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                    Store Crew Shift &amp; Staff Performance
                                </p>
                            </div>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Kelola Jadwal Shift, Absensi, &amp; Kinerja Staf Cabang <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Secara
                                    Transparan</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Hentikan kerumitan mengatur jadwal tukar shift lewat chat WhatsApp yang berantakan. Pantau kehadiran staf toko berbasis GPS/PIN kasir, ukur pencapaian target penjualan per karyawan, dan rekap gaji bulanan tanpa salah hitung.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Coba Modul HRM Staf</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.pos') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Koneksi ke Shift Kasir</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>HR</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>GPS</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>PAY</span>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center">
                                <div class="flex items-center gap-1 text-amber-400">
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <span class="text-xs sm:text-sm font-extrabold text-white ml-1 tabular-nums">4.9 /
                                        5.0</span>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">Disiplin Tim &amp; Efisiensi Payroll Cabang</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Geofence GPS &amp; PIN</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Rotasi Multi-Shift</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Komisi Sales Otomatis</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated Live Store Crew & Shift Roster UI -->
                    <div class="lg:col-span-6 relative w-full max-w-xl mx-auto lg:max-w-none">
                        <!-- Ambient Spotlight Glow behind the Terminal Window -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] sm:rounded-[36px] blur-2xl sm:blur-3xl pointer-events-none -z-10">
                        </div>

                        <!-- Mobile Live Dynamic Island Metric Strip (Clean, non-colliding, zero overlap on Mobile) -->
                        <div class="flex sm:hidden items-center justify-between gap-2 mb-2 w-full">
                            <!-- Mobile Left Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-slate-400 font-medium">Presensi</span>
                                <span class="font-extrabold text-white">100% On-Time</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="users-2" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span class="text-slate-400 font-medium">Shift</span>
                                <span class="font-extrabold text-white">8 Staf Aktif</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Presensi On-Time (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Presensi Karyawan</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">100% On-Time</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>Geofence GPS &amp; PIN Valid</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: Auto-Rekap Komisi (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Payroll &amp; Komisi</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">Auto-Rekap</div>
                            <div class="text-[11px] font-semibold text-[#00C4D8] flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="badge-percent" class="w-3 h-3"></i>
                                <span>Komisi Sales Otomatis</span>
                            </div>
                        </div>

                        <!-- Main Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-2.5 sm:p-4 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-2.5 sm:space-y-3 text-white relative z-10 overflow-hidden mb-6 sm:mb-0">

                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <!-- Mobile Window Header (sm:hidden - Clean title & status, zero truncation) -->
                            <div class="flex sm:hidden items-center justify-between border-b border-white/10 pb-2 gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">Crew Roster &bull; Outlet Senopati</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-semibold shrink-0">
                                    100% Hadir
                                </span>
                            </div>

                            <!-- Tablet & Desktop macOS Window Title Bar (hidden sm:flex) -->
                            <div class="hidden sm:flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]/80"></span>
                                    <div
                                        class="ml-2 flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-[11px] text-slate-300 font-mono">
                                        <i data-lucide="lock" class="w-2.5 h-2.5 text-emerald-400"></i>
                                        <span>cooca.id/app/hrm/crew-roster</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span class="text-[11px] font-medium text-emerald-400 font-mono">100% Hadir Tepat Waktu</span>
                                </div>
                            </div>

                            {{-- Shift Schedule Slots --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5 my-1.5 sm:my-2 text-xs">
                                <div class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 text-[11px] font-semibold text-slate-200">
                                        <span class="truncate">Shift Pagi (07:30 - 15:30)</span>
                                        <span class="text-emerald-400 text-[10px] shrink-0">Aktif</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate">4 Staf: 2 Barista, 1 Kasir, 1 Kitchen</div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[#060B1E]/80 border border-white/10 space-y-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 text-[11px] font-semibold text-slate-200">
                                        <span class="truncate">Shift Sore (15:00 - 23:00)</span>
                                        <span class="text-amber-400 text-[10px] shrink-0">Mendatang</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate">4 Staf: 2 Barista, 1 Kasir, 1 Floor</div>
                                </div>
                            </div>

                            {{-- Staff Performance List --}}
                            <div class="space-y-1.5 text-xs text-left">
                                <div class="text-[10px] uppercase font-mono text-slate-400 px-1">Presensi &amp; Kinerja Staf Kasir:</div>

                                {{-- Employee 1 --}}
                                <div
                                    class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <div
                                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#007AFF]/30 text-[#00C4D8] flex items-center justify-center font-bold text-xs shrink-0">
                                            AF
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-white text-[11px] truncate">Ahmad Fauzi (Kasir Senior)</div>
                                            <div class="text-[10px] text-slate-400 truncate">Masuk: 07:22 WIB • PIN Kasir Valid</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-emerald-400 font-mono text-[11px] whitespace-nowrap">Rp 4.250.000</div>
                                        <div class="text-[10px] text-slate-400 whitespace-nowrap">Komisi: Rp 42.500</div>
                                    </div>
                                </div>

                                {{-- Employee 2 --}}
                                <div
                                    class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <div
                                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            SP
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-white text-[11px] truncate">Siti Rahma (Barista Crew)</div>
                                            <div class="text-[10px] text-slate-400 truncate">Masuk: 07:28 WIB • Geofence GPS</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-blue-400 font-mono text-[11px] whitespace-nowrap">58 Cup Selesai</div>
                                        <div class="text-[10px] text-slate-400 whitespace-nowrap">Target: 96%</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Leave Request Quick Approval --}}
                            <div
                                class="mt-2 p-2.5 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <i data-lucide="calendar" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="text-white font-medium">Permohonan Tukar Shift:</span>
                                        <span class="text-slate-300 text-[11px]"> Budi ↔ Dimas (Sabtu)</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-auto">
                                    <button
                                        class="px-2 py-0.5 rounded bg-white/10 text-slate-300 text-[10px] hover:bg-white/20 transition-colors">Tolak</button>
                                    <button
                                        class="px-2.5 py-0.5 rounded bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-[10px] transition-colors">Setujui</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Masalah SDM di Toko & Cabang Retail --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Tantangan Manajemen Staf
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Apakah Anda Sering Kehilangan Waktu Mengurus Jadwal & Rekap Gaji Karyawan?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Mengelola puluhan staf operasional di banyak lokasi tanpa sistem terpusat sering menimbulkan
                        kesalahpahaman jadwal, keterlambatan yang tidak terpantau, dan sengketa perhitungan komisi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="calendar-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Jadwal Shift Bentrok & Toko Kosong
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Tukar shift dilakukan lewat lisan atau chat pribadi tanpa catatan resmi. Saat jam buka toko,
                            ternyata tidak ada kasir yang hadir karena saling mengira rekannya yang bertugas.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="map-pin-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Titip Absen & Keterlambatan Kronis
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Absensi manual tanda tangan kertas mudah dipalsukan. Staf terlambat 30 menit setiap hari namun
                            tercatat hadir tepat waktu karena ditutupi oleh teman satu shift.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Hitung Gaji & Komisi Makan Waktu
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Setiap tanggal 25, manajer harus menghitung manual jumlah shift kehadiran, potongan
                            keterlambatan, dan persentase komisi kasir dari ribuan lembar struk transaksi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE HRM FEATURES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-white dark:bg-[#0B132B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                    Kemampuan Manajemen SDM COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Disiplin Operasional Tanpa Birokrasi yang Kaku
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Memadukan kemudahan presensi harian staf dengan akurasi data yang dibutuhkan manajemen pusat.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Shift Roster Management (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="calendar-days" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Penyusunan Roster Shift Multi-Cabang
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Susun jadwal kerja tim dengan drag-and-drop sederhana untuk pola shift pagi, siang, malam,
                            maupun split shift. Staf langsung menerima jadwal mereka di smartphone masing-masing, lengkap
                            dengan pemberitahuan pergantian jadwal secara instan.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Distribusi Jadwal:</span>
                        <span class="text-[#007AFF] dark:text-[#00C4D8] font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Otomatis Terhubung ke Login Kasir POS
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Presensi GPS & PIN Kasir (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Absensi Anti Titip Absen
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Karyawan hanya dapat melakukan clock-in saat berada di dalam radius toko yang ditentukan (GPS
                            geofencing) atau dengan memasukkan PIN saat membuka laci kasir POS.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Radius Lokasi Absen</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">Maksimal 50 Meter Toko</span>
                    </div>
                </div>

                {{-- Bento Card 3: Komisi Penjualan Individual (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pencatatan Komisi Otomatis</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Setiap transaksi di kasir dapat diatribusikan ke staf penjual atau teknisi pengerja. Sistem otomatis
                        mengumpulkan nominal komisi sesuai target bulanan yang telah ditetapkan.
                    </p>
                </div>

                {{-- Bento Card 4: Izin, Sakit, & Tukar Shift (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pengajuan Izin & Cuti Digital</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Staf mengajukan surat sakit atau cuti lewat smartphone lengkap dengan foto surat dokter. Supervisor
                        dapat menyetujui langsung dan sistem segera mencari pengganti shift kosong.
                    </p>
                </div>

                {{-- Bento Card 5: Rekap Penggajian Siap Eksekusi (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Rekap Gaji Siap Bayar</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Ekspor rekapitulasi gaji bulanan yang menggabungkan gaji pokok per shift, tunjangan kehadiran,
                        potongan keterlambatan, dan komisi penjualan langsung ke modul Keuangan.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Dari Jadwal Hingga Pembayaran Gaji (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Alur Kerja SDM Terpadu</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Bagaimana Modul HRM Menjalankan Operasional Toko
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Setiap menit kerja karyawan dihargai dengan adil dan terintegrasi dengan pencatatan beban usaha
                        perusahaan.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Roster Diterbitkan</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Manajer toko memetakan kebutuhan shift berdasarkan jam sibuk toko yang tercatat di modul
                            Analitik, memastikan toko tidak kekurangan staf pada akhir pekan.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Presensi Masuk Shift</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Staf tiba di toko dan membuka shift kasir dengan PIN unik. Waktu kedatangan dan modal awal kasir
                            tercatat rapi di sistem secara real-time.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-500/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-blue-500/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Pencatatan Penjualan</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Seluruh transaksi penjualan yang ditangani staf kasir terakumulasi ke profil kerja
                            masing-masing, menciptakan transparansi perhitungan komisi penjualan.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Otomasi ke Beban Gaji</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Di akhir periode, rekap jam kerja dan komisi disetujui owner dan langsung dicatat sebagai Beban
                            Gaji Karyawan di modul Akuntansi & Finance.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 bg-white dark:bg-[#0B132B]">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar
                    Pengelolaan Staf COOCA</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara kerja absensi staf outlet di COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Staf dapat melakukan absensi langsung saat membuka shift di tablet kasir POS menggunakan PIN unik
                        mereka, atau melalui smartphone pribadi dengan verifikasi radius GPS geofencing toko sehingga staf
                        tidak bisa titip absen dari rumah.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah sistem bisa menangani staf yang dipindahkan ke cabang lain?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur penugasan staf ke cabang tertentu secara permanen maupun rotasi
                        harian/mingguan. Saat staf bertugas di Cabang B, hak akses kasir dan presensi mereka otomatis
                        berlaku di cabang tersebut.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah COOCA bisa menghitung komisi penjualan per kasir atau teknisi?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Ya. Setiap transaksi kasir atau pengerjaan order jasa tercatat atas nama staf yang bersangkutan.
                        Anda dapat mengatur skema komisi persentase atau nominal tetap per item/layanan yang terjual.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana data kehadiran ini terhubung ke pembukuan gaji?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Rekap kehadiran (total shift masuk, keterlambatan, dan lembur) otomatis menghasilkan ringkasan gaji
                        bulanan yang dapat langsung disetujui owner dan dibukukan sebagai beban gaji operasional di modul
                        Akuntansi & Finance.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-1">
                        Modul Terkait
                    </h2>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Ekosistem Pengelolaan Tim
                        Operasional</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Buka dan tutup shift kasir dengan
                        rekonsiliasi laci kas akurat.</p>
                </a>

                <a href="{{ route('public.erp.finance') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Keuangan & Payroll
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pencairan pembayaran gaji bulanan staf
                        langsung dari rekening operasional.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Akuntansi Biaya SDM
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jurnal otomatis beban gaji dan komisi
                        penjualan ke pos akun laba rugi.</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Produktivitas
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pantau rata-rata omzet penjualan per jam
                        kerja staf (Sales per Labor Hour).</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Ambient lights inside CTA --}}
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Tertibkan Jadwal & Produktivitas Tim Anda Sekarang
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Bebaskan manajer toko dari kerumitan shift manual dan ciptakan lingkungan kerja yang adil dan
                        transparan dengan COOCA HRM.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Modul HRM
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Konsultasi Kebutuhan Staf
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
