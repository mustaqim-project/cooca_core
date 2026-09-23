@extends('layouts.public_marketing')

@section('title', 'Software Bengkel Motor & Mobil: SPK, Stok Sparepart & Komisi Mekanik | COOCA')
@section('description', 'Solusi sistem manajemen bengkel motor dan mobil. Surat Perintah Kerja (SPK) digital, lacak histori servis plat nomor, potong stok sparepart & oli otomatis, komisi montir, dan reminder WhatsApp.')
@section('keywords', 'software bengkel terintegrasi, aplikasi bengkel motor mobil, sistem manajemen bengkel, software kasir bengkel, surat perintah kerja bengkel spk, komisi mekanik montir, stok sparepart bengkel')

@push('seo')
    <link rel="canonical" href="{{ route('public.solutions.workshop') }}">
    <meta property="og:title" content="Software Bengkel Motor & Mobil: SPK, Stok Sparepart & Komisi Mekanik | COOCA">
    <meta property="og:description" content="Tinggalkan nota kertas minyak. Catat histori servis plat kendaraan, kelola stok oli & suku cadang, dan hitung komisi montir otomatis.">
    <meta property="og:url" content="{{ route('public.solutions.workshop') }}">
    <meta property="og:type" content="product">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software Bengkel Motor & Mobil: SPK, Stok Sparepart & Komisi Mekanik | COOCA">
    <meta name="twitter:description" content="Sistem operasi bengkel modern: SPK digital, inventaris sparepart presisi, pembagian upah mekanik, dan pengingat servis WhatsApp.">

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
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span aria-hidden="true">/</span>
                <span>Solusi Industri</span>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Bengkel & Otomotif</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Narrative & CTA --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-500/10 dark:bg-slate-400/15 border border-slate-500/20 text-xs font-semibold text-slate-700 dark:text-slate-300">
                        <i data-lucide="wrench" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Sistem Operasi Bengkel Motor & Mobil</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Tinggalkan Nota Kertas Minyak. Kendalikan SPK, Sparepart, & Komisi Mekanik
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Ubah bengkel Anda menjadi lebih profesional. Mulai dari pendaftaran nomor polisi kendaraan, penerbitan Surat Perintah Kerja (SPK), pemotongan stok suku cadang & oli otomatis, hingga hitung bagi hasil jasa teknisi tanpa perdebatan di akhir bulan.
                    </p>

                    {{-- Tangible Value Highlights --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Database plat nomor & riwayat servis lengkap</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Stok oli & suku cadang terpotong real-time</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Kalkulasi komisi montir otomatis per job</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Pengingat ganti oli berkala via WhatsApp</span>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('register') }}" class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <span>Mulai Coba Sistem Bengkel</span>
                            <i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20saya%20tertarik%20dengan%20solusi%20Bengkel%20COOCA" target="_blank" rel="noopener" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                            <span>Tanya Solusi Bengkel</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Simulated Apple Bento Workshop Service Station --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">SPK #WO-8821 - Sedang Pengerjaan</span>
                            </div>
                            <span class="text-[11px] font-semibold text-blue-600 bg-blue-500/10 px-2.5 py-0.5 rounded-full">
                                Pit 02 (Mekanik: Doni)
                            </span>
                        </div>

                        {{-- Vehicle Badge --}}
                        <div class="p-3.5 rounded-[14px] bg-neutral-900 text-white flex items-center justify-between text-xs font-mono">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="car" class="w-5 h-5 text-amber-400" aria-hidden="true"></i>
                                <div>
                                    <p class="font-bold text-white tracking-wider">B 4821 KFA</p>
                                    <p class="text-[10px] text-neutral-400">Honda Vario 160 • Odo: 14.820 KM</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-white/10 px-2 py-0.5 rounded text-neutral-300">Servis Berkala</span>
                        </div>

                        {{-- Itemized Parts & Services List --}}
                        <div class="space-y-3 text-xs">
                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">Oli Mesin Matic Fully Synthetic 0.8L</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Suku Cadang (Stok Potong Rak B2)</p>
                                    </div>
                                    <span class="font-mono">Rp 65.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>HPP Part: Rp 48.000</span>
                                    <span>Sisa Gudang: 12 Btl</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                                <div class="flex justify-between items-start font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <div>
                                        <p class="font-bold">Jasa Servis CVT & Ganti Oli</p>
                                        <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B] font-normal">Biaya Jasa Bengkel</p>
                                    </div>
                                    <span class="font-mono">Rp 55.000</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-blue-600 dark:text-blue-400 font-mono pt-1 border-t border-dashed border-neutral-200 dark:border-neutral-700/60">
                                    <span>Komisi Mekanik (40%): Rp 22.000</span>
                                    <span>Tercatat ke Doni</span>
                                </div>
                            </div>
                        </div>

                        {{-- Calculation Breakdown --}}
                        <div class="p-4 rounded-[16px] bg-neutral-50/80 dark:bg-neutral-800/50 border border-neutral-200/60 dark:border-neutral-800 space-y-2 text-xs">
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Total Sparepart (1 Item)</span>
                                <span class="font-mono">Rp 65.000</span>
                            </div>
                            <div class="flex justify-between text-[#6E6E73] dark:text-[#86868B]">
                                <span>Total Ongkos Jasa (1 Item)</span>
                                <span class="font-mono">Rp 55.000</span>
                            </div>
                            <div class="flex justify-between text-[#1D1D1F] dark:text-[#F5F5F7] font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                                <span>Total Tagihan Pelanggan</span>
                                <span class="font-mono text-sm text-[#007AFF]">Rp 120.000</span>
                            </div>
                        </div>

                        {{-- Quick Operations Status --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">Cetak SPK & Nota</span>
                            </div>
                            <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 flex items-center gap-2">
                                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600" aria-hidden="true"></i>
                                <span class="font-medium text-[#1D1D1F] dark:text-[#F5F5F7]">WA Siap Ambil</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Deep Sector Pain Points --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-rose-500/[0.03] dark:bg-rose-500/[0.06] border border-rose-500/15 space-y-8">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-rose-600 dark:text-rose-400 block">
                        Tantangan Pengelolaan Bengkel
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Masalah Klasik yang Kerap Menimbulkan Kerugian dan Salah Paham
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">01</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Sparepart & Oli Bocor Tanpa Nota</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Mekanik mengambil busi, kampas rem, atau oli dari gudang tetapi lupa dicatat di nota kasir, membuat stok fisik habis saat dibutuhkan pelanggan lain.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">02</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Perselisihan Upah Komisi Montir</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Penghitungan komisi jasa mekanik manual di akhir bulan sering memicu kecurigaan antar montir mengenai siapa yang mengerjakan servis tertentu.
                        </p>
                    </div>

                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/70 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-8 h-8 rounded-[10px] bg-rose-500/10 text-rose-600 font-bold text-xs flex items-center justify-center font-mono">03</div>
                        <h3 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pelanggan Hilang Setelah Servis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Tidak adanya database histori nomor polisi membuat bengkel tidak tahu kapan jadwal ganti oli pelanggan berikutnya, kehilangan peluang repeat order bernilai jutaan rupiah.
                        </p>
                    </div>
                </div>
            </section>

            {{-- 6 Specialized Features (Bento Grid) --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Kapabilitas Khusus Bengkel
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Fitur Spesifik untuk Operasional Bengkel Roda Dua & Empat
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Surat Perintah Kerja (SPK) Digital</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Catat keluhan pemilik kendaraan, kilometer (KM) terkini, dan estimasi biaya perbaikan sebelum montir membongkar mesin, menghindari salah paham saat pembayaran.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="car" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Histori Kendaraan per Plat Nomor</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Cukup ketik plat nomor (contoh: B 4821 KFA), montir langsung dapat melihat riwayat suku cadang apa saja yang pernah diganti dan tanggal servis terakhir.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="package-search" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Stok Sparepart & Oli Real-Time</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Sistem secara otomatis mengurangi stok fisik di gudang begitu part dimasukkan ke dalam SPK aktif. Tersedia peringatan stok minimum untuk order ke distributor.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="users" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Perhitungan Komisi Montir Otomatis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Bagi hasil upah jasa mekanik (misal: 30% atau 40% dari ongkos pasang) dihitung presisi per SPK yang selesai. Rekap slip gaji montir transparan dan bebas sengketa.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="bell-ring" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Reminder Servis & Oli via WhatsApp</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Kirim pesan ramah otomatis ke WhatsApp pelanggan 60 hari setelah servis: "Kendaraan B 4821 KFA sudah waktunya ganti oli berkala agar performa mesin tetap prima."
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-[22px] space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="receipt" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Nota Terperinci Jasa & Part</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Struk cetak kasir membedakan dengan jelas rincian harga suku cadang dan ongkos jasa teknisi, lengkap dengan garansi servis untuk meningkatkan kepercayaan pelanggan.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Operational Flow / Connected System Architecture --}}
            <section class="p-6 sm:p-10 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-8 shadow-sm">
                <div class="max-w-2xl">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Alur Ekosistem Bengkel
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1">
                        Bagaimana COOCA Menghubungkan Front Office, Gudang, & Teknisi
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-[#007AFF]">Langkah 01</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Input Plat & Keluhan</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Front desk mencatat KM kendaraan dan mencetak SPK ke mekanik.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-amber-500">Langkah 02</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pengambilan Sparepart</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Stok oli & part terpotong langsung dari gudang saat dipasang.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-emerald-500">Langkah 03</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Selesai & Kasir Cetak</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Pelanggan membayar invoice dan komisi montir otomatis tercatat.</p>
                    </div>
                    <div class="p-4 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-2">
                        <div class="text-xs font-mono font-bold text-purple-500">Langkah 04</div>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Automated WA CRM</h4>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">Sistem menjadwalkan notifikasi WhatsApp servis berkala secara cerdas.</p>
                    </div>
                </div>
            </section>

            {{-- Sector Specific FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Umum Seputar COOCA Bengkel</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah satu SPK bisa dikerjakan oleh lebih dari satu mekanik?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Bisa. Anda dapat mengalokasikan mekanik berbeda untuk setiap item pekerjaan jasa. Misalnya, pengerjaan kelistrikan dialokasikan ke Mekanik A, sementara servis suspensi/kaki-kaki dialokasikan ke Mekanik B. Komisi masing-masing montir akan dihitung secara proporsional.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah COOCA cocok untuk bengkel motor maupun bengkel mobil?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Ya. Sistem dirancang fleksibel untuk bengkel motor umum, authorized dealer, bengkel mobil, bengkel AC mobil, variasi/aksesoris, hingga spesialis ban (spooring-balancing). Kolom data nomor rangka, nomor mesin, dan kilometer dapat disesuaikan.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana cara mencatat oli curah (drum) yang dijual per liter?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA mendukung multi-satuan dengan nilai desimal. Anda dapat membeli oli dalam kemasan drum 200 liter, lalu menjualnya per 0.8 liter, 1 liter, atau 3.5 liter ke pelanggan saat servis. Stok drum berkurang secara presisi tanpa pembulatan kasar.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana mencetak struk nota servis agar terlihat resmi?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            COOCA mendukung cetak struk thermal 58mm/80mm untuk printer kasir kasir cepat, serta invoice format A4/A5 menggunakan printer inkjet biasa lengkap dengan logo bengkel, tanda tangan mekanik, dan ketentuan garansi perbaikan.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Related Modules & Vertical Cross Links --}}
            <section class="border-t border-neutral-200/80 dark:border-neutral-800 pt-12 space-y-6">
                <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Jelajahi Solusi Industri & Modul Terkait</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('public.erp.inventory') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Stok</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Stok Suku Cadang & Oli</h4>
                    </a>
                    <a href="{{ route('public.erp.pos') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Modul Kasir</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Kasir Kasbon & QRIS</h4>
                    </a>
                    <a href="{{ route('public.solutions.services') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Bisnis Jasa & Servis</h4>
                    </a>
                    <a href="{{ route('public.solutions.manufacturing') }}" class="bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-5 rounded-[18px] group hover:border-[#007AFF]/40 hover:shadow-sm transition-all">
                        <span class="text-xs uppercase font-bold text-[#007AFF]">Solusi Industri</span>
                        <h4 class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] mt-1.5 group-hover:text-[#007AFF] transition-colors">Manufaktur & Pabrikasi</h4>
                    </a>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Solusi Teruji untuk Ratusan Bengkel Motor & Mobil Indonesia</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Mulai Operasikan Bengkel Anda dengan Standar Modern
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Daftar akun COOCA hari ini dan rasakan kemudahan mengontrol SPK servis kendaraan, suku cadang, dan upah montir secara otomatis.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ route('register') }}" class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <span>Coba Software Bengkel Gratis</span>
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
