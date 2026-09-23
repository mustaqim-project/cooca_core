@extends('layouts.public_marketing')

@section('title', 'Software Manajemen Karyawan, Shift & Absensi Outlet | COOCA')
@section('description', 'Aplikasi manajemen staf dan SDM untuk toko ritel, F&B, dan jasa. Atur jadwal shift kerja,
    presensi digital GPS, pantau produktivitas penjualan kasir, dan rekap penggajian berbasis kehadiran.')
@section('keywords', 'software hrm karyawan outlet, aplikasi jadwal shift kasir, absensi gps toko, manajemen sdm retail,
    rekap gaji karyawan umkm')

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
    <div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

        {{-- Ambient Light Accent --}}
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1300px] h-[480px] bg-gradient-to-b from-teal-500/10 via-emerald-500/5 to-transparent blur-3xl pointer-events-none -z-10">
        </div>

        {{-- Breadcrumb --}}
        <nav class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a>
                </li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Manajemen Staf & Shift
                    (HRM)</li>
            </ol>
        </nav>

        {{-- 1. HERO SECTION (2 Columns) --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-50 dark:bg-teal-950/60 border border-teal-200/60 dark:border-teal-800/40 text-teal-700 dark:text-teal-400 text-xs font-semibold tracking-wide">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Store Crew Shift & Staff Performance</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                        Kelola Jadwal Shift, Absensi, & Kinerja Staf Cabang <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-500">Secara
                            Transparan</span>
                    </h1>

                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                        Hentikan kerumitan mengatur jadwal tukar shift lewat chat WhatsApp yang berantakan. Pantau kehadiran
                        staf toko berbasis GPS/PIN kasir, ukur pencapaian target penjualan per karyawan, dan rekap gaji
                        bulanan tanpa salah hitung.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                            <span>Coba Modul HRM Staf</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.erp.pos') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                            <span>Koneksi ke Shift Kasir</span>
                        </a>
                    </div>

                    {{-- Key Operations Metrics --}}
                    <div
                        class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Validasi Absensi</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Geofence & PIN</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Penjadwalan</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Rotasi Multi-Shift</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Penghitungan Komisi
                            </div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Otomatis Per Sales</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live Store Crew & Shift Roster UI --}}
                <div class="lg:col-span-6">
                    <div
                        class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">

                        {{-- Header Branch Staff Control --}}
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-teal-500/20 text-teal-400">
                                    <i data-lucide="users-2" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-neutral-200">Outlet Senopati — Roster Hari Ini</div>
                                    <div class="text-[10px] text-neutral-500">Rabu, 23 September 2026 • 8 Staf Bertugas
                                    </div>
                                </div>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">100%
                                Hadir Tepat Waktu</span>
                        </div>

                        {{-- Shift Schedule Slots --}}
                        <div class="grid grid-cols-2 gap-2 my-3 text-xs">
                            <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-semibold text-neutral-300">
                                    <span>Shift Pagi (07:30 - 15:30)</span>
                                    <span class="text-emerald-400 text-[10px]">Aktif</span>
                                </div>
                                <div class="text-[10px] text-neutral-500">4 Staf: 2 Barista, 1 Kasir, 1 Kitchen</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-neutral-950/80 border border-neutral-800 space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-semibold text-neutral-300">
                                    <span>Shift Sore (15:00 - 23:00)</span>
                                    <span class="text-amber-400 text-[10px]">Mendatang</span>
                                </div>
                                <div class="text-[10px] text-neutral-500">4 Staf: 2 Barista, 1 Kasir, 1 Floor</div>
                            </div>
                        </div>

                        {{-- Staff Performance List --}}
                        <div class="space-y-1.5 text-xs text-left">
                            <div class="text-[10px] uppercase font-mono text-neutral-500 px-1">Presensi & Kinerja Staf
                                Kasir:</div>

                            {{-- Employee 1 --}}
                            <div
                                class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-full bg-teal-600/30 text-teal-400 flex items-center justify-center font-bold text-xs">
                                        AF</div>
                                    <div>
                                        <div class="font-medium text-white text-[11px]">Ahmad Fauzi (Kasir Senior)</div>
                                        <div class="text-[10px] text-neutral-400">Masuk: 07:22 WIB • PIN Kasir Terverifikasi
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-emerald-400 font-mono text-[11px]">Rp 4.250.000 (34 Trx)
                                    </div>
                                    <div class="text-[10px] text-neutral-500">Komisi: Rp 42.500</div>
                                </div>
                            </div>

                            {{-- Employee 2 --}}
                            <div
                                class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs">
                                        SP</div>
                                    <div>
                                        <div class="font-medium text-white text-[11px]">Siti Rahma (Barista Crew)</div>
                                        <div class="text-[10px] text-neutral-400">Masuk: 07:28 WIB • Geofence GPS Toko</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-blue-400 font-mono text-[11px]">58 Cup Selesai</div>
                                    <div class="text-[10px] text-neutral-500">Target Harian: 96%</div>
                                </div>
                            </div>
                        </div>

                        {{-- Leave Request Quick Approval --}}
                        <div
                            class="mt-3 p-2.5 rounded-xl bg-teal-950/30 border border-teal-800/40 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-teal-400"></i>
                                <div>
                                    <span class="text-white font-medium">Permohonan Izin Tukar Shift:</span>
                                    <span class="text-neutral-400 text-[11px]"> Budi ↔ Dimas (Sabtu Depan)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button
                                    class="px-2 py-0.5 rounded bg-neutral-800 text-neutral-300 text-[10px] hover:bg-neutral-700">Tolak</button>
                                <button
                                    class="px-2.5 py-0.5 rounded bg-teal-600 text-white font-bold text-[10px] hover:bg-teal-500">Setujui</button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        {{-- 2. PAIN POINTS: Masalah SDM di Toko & Cabang Retail --}}
        <section
            class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-teal-600 dark:text-teal-400 font-semibold mb-3">
                        Tantangan Manajemen Staf</h2>
                    <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Apakah Anda Sering Kehilangan Waktu Mengurus Jadwal & Rekap Gaji Karyawan?
                    </p>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                        Mengelola puluhan staf operasional di banyak lokasi tanpa sistem terpusat sering menimbulkan
                        kesalahpahaman jadwal, keterlambatan yang tidak terpantau, dan sengketa perhitungan komisi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="calendar-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Jadwal Shift Bentrok & Toko Kosong
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Tukar shift dilakukan lewat lisan atau chat pribadi tanpa catatan resmi. Saat jam buka toko,
                            ternyata tidak ada kasir yang hadir karena saling mengira rekannya yang bertugas.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="map-pin-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Titip Absen & Keterlambatan Kronis
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Absensi manual tanda tangan kertas mudah dipalsukan. Staf terlambat 30 menit setiap hari namun
                            tercatat hadir tepat waktu karena ditutupi oleh teman satu shift.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-900/50 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Hitung Gaji & Komisi Makan Waktu
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Setiap tanggal 25, manajer harus menghitung manual jumlah shift kehadiran, potongan
                            keterlambatan, dan persentase komisi kasir dari ribuan lembar struk transaksi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE HRM FEATURES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-teal-600 dark:text-teal-400 font-semibold mb-3">Kemampuan
                    Manajemen SDM COOCA</h2>
                <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Disiplin Operasional Tanpa Birokrasi yang Kaku
                </p>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                    Memadukan kemudahan presensi harian staf dengan akurasi data yang dibutuhkan manajemen pusat.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Shift Roster Management (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <i data-lucide="calendar-days" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Penyusunan Roster Shift Multi-Cabang
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Susun jadwal kerja tim dengan drag-and-drop sederhana untuk pola shift pagi, siang, malam,
                            maupun split shift. Staf langsung menerima jadwal mereka di smartphone masing-masing, lengkap
                            dengan pemberitahuan pergantian jadwal secara instan.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                        <span class="text-neutral-600 dark:text-neutral-300 font-medium">Distribusi Jadwal:</span>
                        <span class="text-teal-600 dark:text-teal-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Otomatis Terhubung ke Login Kasir POS
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Presensi GPS & PIN Kasir (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Absensi Anti Titip Absen
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Karyawan hanya dapat melakukan clock-in saat berada di dalam radius toko yang ditentukan (GPS
                            geofencing) atau dengan memasukkan PIN saat membuka laci kasir POS.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                        <span class="text-neutral-500">Radius Lokasi Absen</span>
                        <span class="text-emerald-500 font-bold">Maksimal 50 Meter Toko</span>
                    </div>
                </div>

                {{-- Bento Card 3: Komisi Penjualan Individual (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pencatatan Komisi Otomatis</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Setiap transaksi di kasir dapat diatribusikan ke staf penjual atau teknisi pengerja. Sistem otomatis
                        mengumpulkan nominal komisi sesuai target bulanan yang telah ditetapkan.
                    </p>
                </div>

                {{-- Bento Card 4: Izin, Sakit, & Tukar Shift (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pengajuan Izin & Cuti Digital</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Staf mengajukan surat sakit atau cuti lewat smartphone lengkap dengan foto surat dokter. Supervisor
                        dapat menyetujui langsung dan sistem segera mencari pengganti shift kosong.
                    </p>
                </div>

                {{-- Bento Card 5: Rekap Penggajian Siap Eksekusi (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Rekap Gaji Siap Bayar</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ekspor rekapitulasi gaji bulanan yang menggabungkan gaji pokok per shift, tunjangan kehadiran,
                        potongan keterlambatan, dan komisi penjualan langsung ke modul Keuangan.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Dari Jadwal Hingga Pembayaran Gaji --}}
        <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 text-teal-400 text-xs font-semibold mb-3 border border-teal-500/30">
                        <span>Alur Kerja SDM Terpadu</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Bagaimana Modul HRM Menjalankan Operasional Toko
                    </h2>
                    <p class="text-neutral-400 text-sm sm:text-base mt-3">
                        Setiap menit kerja karyawan dihargai dengan adil dan terintegrasi dengan pencatatan beban usaha
                        perusahaan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-teal-600/30 text-teal-400 flex items-center justify-center font-bold text-xs border border-teal-500/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Roster Diterbitkan</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Manajer toko memetakan kebutuhan shift berdasarkan jam sibuk toko yang tercatat di modul
                            Analitik, memastikan toko tidak kekurangan staf pada akhir pekan.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Presensi Masuk Shift</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Staf tiba di toko dan membuka shift kasir dengan PIN unik. Waktu kedatangan dan modal awal kasir
                            tercatat rapi di sistem secara real-time.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Pencatatan Penjualan</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Seluruh transaksi penjualan yang ditangani staf kasir terakumulasi ke profil kerja
                            masing-masing, menciptakan transparansi perhitungan komisi penjualan.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Otomasi ke Beban Gaji</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Di akhir periode, rekap jam kerja dan komisi disetujui owner dan langsung dicatat sebagai Beban
                            Gaji Karyawan di modul Akuntansi & Finance.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-teal-600 dark:text-teal-400 font-semibold mb-2">
                    Pertanyaan Umum</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Pengelolaan
                    Staf COOCA</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana cara kerja absensi staf outlet di COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Staf dapat melakukan absensi langsung saat membuka shift di tablet kasir POS menggunakan PIN unik
                        mereka, atau melalui smartphone pribadi dengan verifikasi radius GPS geofencing toko sehingga staf
                        tidak bisa titip absen dari rumah.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah sistem bisa menangani staf yang dipindahkan ke cabang lain?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur penugasan staf ke cabang tertentu secara permanen maupun rotasi
                        harian/mingguan. Saat staf bertugas di Cabang B, hak akses kasir dan presensi mereka otomatis
                        berlaku di cabang tersebut.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah COOCA bisa menghitung komisi penjualan per kasir atau teknisi?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Ya. Setiap transaksi kasir atau pengerjaan order jasa tercatat atas nama staf yang bersangkutan.
                        Anda dapat mengatur skema komisi persentase atau nominal tetap per item/layanan yang terjual.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana data kehadiran ini terhubung ke pembukuan gaji?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Rekap kehadiran (total shift masuk, keterlambatan, dan lembur) otomatis menghasilkan ringkasan gaji
                        bulanan yang dapat langsung disetujui owner dan dibukukan sebagai beban gaji operasional di modul
                        Akuntansi & Finance.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-teal-600 dark:text-teal-400 font-semibold mb-1">Modul
                        Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Pengelolaan Tim
                        Operasional</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-teal-600 dark:text-teal-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-teal-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-teal-600 transition-colors">
                        Point of Sale (POS)</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Buka dan tutup shift kasir dengan
                        rekonsiliasi laci kas akurat.</p>
                </a>

                <a href="{{ route('public.erp.finance') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-teal-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-teal-600 transition-colors">
                        Keuangan & Payroll</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pencairan pembayaran gaji bulanan staf
                        langsung dari rekening operasional.</p>
                </a>

                <a href="{{ route('public.erp.accounting') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-teal-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-teal-600 transition-colors">
                        Akuntansi Biaya SDM</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Jurnal otomatis beban gaji dan komisi
                        penjualan ke pos akun laba rugi.</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-teal-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-teal-600 transition-colors">
                        Analitik Produktivitas</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pantau rata-rata omzet penjualan per jam
                        kerja staf (Sales per Labor Hour).</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Tertibkan Jadwal & Produktivitas Tim Anda Sekarang
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-400">
                        Bebaskan manajer toko dari kerumitan shift manual dan ciptakan lingkungan kerja yang adil dan
                        transparan dengan COOCA HRM.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-semibold text-sm transition-all shadow-md">
                            Coba Demo Modul HRM
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                            Konsultasi Kebutuhan Staf
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
