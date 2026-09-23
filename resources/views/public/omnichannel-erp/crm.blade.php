@extends('layouts.public_marketing')

@section('title', 'Software CRM & Sistem Loyalitas Pelanggan Terintegrasi POS | COOCA')
@section('description', 'Aplikasi CRM dan database pelanggan multi-cabang untuk bisnis retail & jasa. Bangun program membership poin, lacak riwayat belanja omnichannel, dan segmentasi otomatis untuk meningkatkan repeat order.')
@section('keywords', 'software crm pelanggan, sistem membership loyalitas poin, database pelanggan retail, aplikasi retensi pelanggan, crm terintegrasi pos')

@push('seo')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA CRM & Customer Loyalty",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Platform database pelanggan 360 derajat terintegrasi kasir POS dan channel online untuk mendorong retensi dan repeat order pelanggan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Database pelanggan terpusat dari kasir outlet fisik, website, dan marketplace",
    "Riwayat transaksi lengkap, produk favorit, dan total belanja (Customer Lifetime Value)",
    "Program membership berjenjang (Tier Silver, Gold, Platinum) dan reward poin",
    "Segmentasi otomatis berbasis aktivitas belanja (Pelanggan Aktif vs At-Risk)",
    "Integrasi pengiriman pesan promo personal langsung ke WhatsApp pelanggan"
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Bagaimana kasir toko mendaftarkan pelanggan baru saat transaksi sedang ramai?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat cepat. Kasir cukup meminta nomor WhatsApp dan nama panggilan pelanggan di layar POS dalam waktu 5 detik. Profil pelanggan langsung aktif, poin transaksi pertama langsung masuk, dan pelanggan dapat menerima nota digital via WhatsApp."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah poin belanja bisa digunakan di cabang outlet yang berbeda?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, poin berlaku universal di seluruh cabang yang terhubung dengan akun COOCA Anda. Pelanggan yang berbelanja di Cabang A dapat menukarkan poin diskonnya saat bertransaksi di Cabang B."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah data kontak pelanggan saya aman jika ada staf atau kasir yang mengundurkan diri?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat aman. Database pelanggan tersimpan di cloud terpusat milik perusahaan. Staf kasir hanya bisa melihat nama dan sisa poin saat melayani pembayaran, dan hak akses untuk mengekspor database nomor telepon hanya dimiliki oleh Owner atau Admin yang diberi izin khusus."
      }
    },
    {
      "@type": "Question",
      "name": "Bisakah kami mengimpor database kontak pelanggan lama dari file Excel?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tentu bisa. COOCA menyediakan template impor Excel untuk memasukkan daftar nama, nomor telepon, alamat, dan saldo poin awal pelanggan lama Anda secara massal dalam hitungan menit."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

    {{-- Ambient Light Accent --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-purple-500/10 via-pink-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
            <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li><a href="{{ route('public.erp.erp') }}" class="hover:text-blue-600 transition-colors">Omnichannel ERP</a></li>
            <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
            <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">CRM & Loyalitas Pelanggan</li>
        </ol>
    </nav>

    {{-- 1. HERO SECTION (2 Columns) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            {{-- Left Column: Copy & Value Proposition --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-50 dark:bg-purple-950/60 border border-purple-200/60 dark:border-purple-800/40 text-purple-700 dark:text-purple-400 text-xs font-semibold tracking-wide">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Customer 360° & Loyalty Points Engine</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                    Ubah Pembeli Sekali Datang Menjadi <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-rose-500">Pelanggan Setia yang Terus Kembali</span>
                </h1>

                <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                    Biaya mencari pelanggan baru jauh lebih mahal daripada mempertahankan pelanggan lama. COOCA CRM menyatukan riwayat belanja pelanggan dari kasir toko fisik dan channel online, mengelola poin reward, serta memicu repeat order secara teratur.
                </p>

                {{-- Action CTAs --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="{{ route('public.demo') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                        <span>Coba Modul CRM Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('public.erp.pos') }}" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                        <span>Lihat Integrasi Kasir POS</span>
                    </a>
                </div>

                {{-- Key Retention Specs --}}
                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Pengenal Utama</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Nomor WhatsApp</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Sistem Reward</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Poin & Tier Member</div>
                    </div>
                    <div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Segmentasi</div>
                        <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">RFM Otomatis</div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Simulated Live Customer 360° Profile UI --}}
            <div class="lg:col-span-6">
                <div class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">
                    
                    {{-- Profile Header Card --}}
                    <div class="p-3.5 rounded-xl bg-neutral-950/90 border border-neutral-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-purple-600 to-pink-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                NS
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-white text-sm">Nadia Saraswati</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/40">VIP Gold</span>
                                </div>
                                <div class="text-[11px] text-neutral-400 font-mono mt-0.5">+62 812-9844-xxxx • ID: CUST-8821</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-neutral-400">Saldo Poin</div>
                            <div class="text-base font-bold text-purple-400 font-mono">845 Pts</div>
                        </div>
                    </div>

                    {{-- Customer Lifetime Stats Grid --}}
                    <div class="grid grid-cols-3 gap-2 my-3 text-center">
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80">
                            <div class="text-[10px] text-neutral-400">Total Belanja (LTV)</div>
                            <div class="text-xs font-bold text-white font-mono mt-0.5">Rp 8.450.000</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80">
                            <div class="text-[10px] text-neutral-400">Frekuensi Belanja</div>
                            <div class="text-xs font-bold text-white font-mono mt-0.5">18 Transaksi</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80">
                            <div class="text-[10px] text-neutral-400">Rata-rata Basket Size</div>
                            <div class="text-xs font-bold text-white font-mono mt-0.5">Rp 469.000</div>
                        </div>
                    </div>

                    {{-- Recent Omnichannel Purchase Timeline --}}
                    <div class="space-y-2 text-xs">
                        <div class="text-[10px] uppercase font-mono text-neutral-500 px-1">Riwayat Transaksi Lintas Channel:</div>

                        <div class="p-2.5 rounded-xl bg-neutral-950/40 border border-neutral-800/60 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded bg-blue-500/20 text-blue-400">
                                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                </span>
                                <div>
                                    <div class="font-medium text-white text-[11px]">POS Outlet Sudirman</div>
                                    <div class="text-[10px] text-neutral-500">2x Croissant Butter, 2x Kopi Susu Aren</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-white font-mono">Rp 100.000</div>
                                <div class="text-[10px] text-purple-400">+10 Poin</div>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-neutral-950/40 border border-neutral-800/60 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="p-1 rounded bg-emerald-500/20 text-emerald-400">
                                    <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                </span>
                                <div>
                                    <div class="font-medium text-white text-[11px]">Website Online Store</div>
                                    <div class="text-[10px] text-neutral-500">1x Kopi Biji Arabika 1kg (Kirim ke Rumah)</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-white font-mono">Rp 280.000</div>
                                <div class="text-[10px] text-purple-400">+28 Poin</div>
                            </div>
                        </div>
                    </div>

                    {{-- Retention Action Trigger --}}
                    <div class="mt-3 p-2.5 rounded-xl bg-purple-950/30 border border-purple-800/40 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
                            <div>
                                <span class="text-white font-medium">Ulang Tahun Minggu Depan:</span>
                                <span class="text-neutral-400 text-[11px]"> Kirim reward voucher personal</span>
                            </div>
                        </div>
                        <button class="px-3 py-1 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-bold text-[10px] flex items-center gap-1 transition-colors">
                            <i data-lucide="message-square" class="w-3 h-3"></i>
                            <span>Kirim WA</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 2. PAIN POINTS: Mengapa Bisnis Kehilangan Pelanggan Terbaiknya --}}
    <section class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-3">Tantangan Retensi Konsumen</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Apakah Anda Tahu Siapa 20% Pelanggan yang Menyumbang 80% Omzet Toko Anda?
                </p>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                    Kebanyakan bisnis melayani ratusan orang setiap hari, namun tidak pernah mencatat siapa mereka sehingga tidak bisa menghubungi mereka kembali saat toko sedang sepi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pain 1 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <i data-lucide="user-x" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Pelanggan Setia Hilang Tanpa Disadari</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Seorang pelanggan yang biasanya datang seminggu dua kali tiba-tiba tidak pernah muncul lagi selama 2 bulan. Tanpa sistem CRM, Anda baru menyadarinya saat mereka sudah beralih ke kompetitor.
                    </p>
                </div>

                {{-- Pain 2 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="database-zap" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Data Kontak Dibawa Kabur Staf</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Nomor kontak klien hanya tersimpan di WhatsApp pribadi karyawan penjualan atau kasir. Begitu staf tersebut resign, seluruh hubungan bisnis dengan pelanggan tersebut terputus total.
                    </p>
                </div>

                {{-- Pain 3 --}}
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="megaphone-off" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Broadcast Promo Sembarangan (Spam)</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Mengirim pesan promo yang sama ke semua orang tanpa segmentasi. Pelanggan merasa terganggu karena penawaran tidak relevan, hingga akhirnya memblokir nomor WhatsApp bisnis Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. CORE CRM FEATURES: Bento Apple HIG --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-3">Fitur CRM & Retensi Pelanggan</h2>
            <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                Membangun Komunitas Pelanggan yang Terus Bertransaksi
            </p>
            <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                Dirancang untuk memudahkan bisnis retail, cafe, klinik, dan bengkel mengelola hubungan personal dalam skala besar.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Bento Card 1: 360 Unified Profile (Span 7) --}}
            <div class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="contact-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Profil Pelanggan 360° yang Lengkap
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ketahui siapa pelanggan Anda secara mendalam: riwayat belanja offline dan online, produk yang paling sering dibeli, tanggal ulang tahun, catatan alergi/preferensi khusus, serta total nominal belanja seumur hidup (Customer Lifetime Value).
                    </p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                    <span class="text-neutral-600 dark:text-neutral-300 font-medium">Pengenal Universal:</span>
                    <span class="text-purple-600 dark:text-purple-400 font-semibold flex items-center gap-1">
                        <i data-lucide="check" class="w-4 h-4"></i> Satu Nomor HP untuk Seluruh Cabang & Online
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Loyalty Points & Tier Member (Span 5) --}}
            <div class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-11 h-11 rounded-2xl bg-pink-100 dark:bg-pink-950 flex items-center justify-center text-pink-600 dark:text-pink-400">
                        <i data-lucide="gift" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Program Poin & Membership Tier
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Berikan apresiasi nyata kepada pelanggan. Atur aturan perolehan poin per kelipatan belanja dan buat tingkatan member (Silver, Gold, Platinum) dengan benefit diskon khusus yang memotivasi mereka untuk berbelanja lebih banyak.
                    </p>
                </div>

                <div class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                    <span class="text-neutral-500">Tier Naik Otomatis</span>
                    <span class="text-pink-500 font-bold">Belanja &gt; Rp 5 Juta → Gold</span>
                </div>
            </div>

            {{-- Bento Card 3: Segmentasi Cerdas RFM (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Segmentasi Otomatis (RFM)</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Sistem mengelompokkan pelanggan secara pintar: Pelanggan Setia (Loyal), Pelanggan Baru, Pelanggan Nilai Tinggi (Big Spenders), hingga Pelanggan Pasif (At-Risk) yang sudah lama tidak berkunjung.
                </p>
            </div>

            {{-- Bento Card 4: Pemicu Pesan Otomatis (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pemicu Promo Otomatis</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Kirim pesan otomatis saat ulang tahun pelanggan, ucapan terima kasih setelah pembelian pertama, atau voucher pengingat bagi pelanggan yang belum berkunjung lebih dari 30 hari.
                </p>
            </div>

            {{-- Bento Card 5: Keamanan & Hak Akses Data (Span 4) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Keamanan Database Aset</h3>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Data nomor HP dan email pelanggan terlindungi. Hanya manajemen pusat yang memiliki otorisasi untuk mengekspor database, mencegah kebocoran kontak bisnis ke pihak luar.
                </p>
            </div>

        </div>
    </section>

    {{-- 4. CONNECTED CHAIN: Siklus Retensi Pelanggan --}}
    <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-400 text-xs font-semibold mb-3 border border-purple-500/30">
                    <span>Siklus Hubungan Pelanggan Berkelanjutan</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Bagaimana COOCA Membantu Bisnis Mempertahankan Pelanggan
                </h2>
                <p class="text-neutral-400 text-sm sm:text-base mt-3">
                    Setiap interaksi pelanggan di kasir maupun online diubah menjadi data yang dapat ditindaklanjuti untuk menghasilkan penjualan berikutnya.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">1</div>
                    <h3 class="text-base font-bold text-white">Input Mudah di Kasir</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Saat bayar, kasir cukup menanyakan nomor WhatsApp. Pelanggan langsung terdaftar tanpa formulir kertas yang merepotkan dan langsung mendapatkan poin belanja.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-600/30 text-pink-400 flex items-center justify-center font-bold text-xs border border-pink-500/40">2</div>
                    <h3 class="text-base font-bold text-white">Riwayat Terkonsolidasi</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Setiap transaksi berikutnya—baik di cabang lain maupun melalui toko online—otomatis menambahkan riwayat belanja ke profil pelanggan yang sama.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">3</div>
                    <h3 class="text-base font-bold text-white">Segmentasi Cerdas</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        COOCA memetakan siapa saja pelanggan yang loyal dan siapa yang sudah lama tidak berbelanja, sehingga promo dapat ditargetkan dengan tepat sasaran.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">4</div>
                    <h3 class="text-base font-bold text-white">Repeat Order Teratur</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Pelanggan menerima penawaran produk favorit atau pengingat penukaran poin reward sebelum kedaluwarsa, mendorong mereka datang kembali ke toko Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-2">Pertanyaan Umum</h2>
            <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar CRM & Database Pelanggan</p>
        </div>

        <div class="space-y-4">
            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bagaimana kasir toko mendaftarkan pelanggan baru saat transaksi sedang ramai?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Sangat cepat. Kasir cukup meminta nomor WhatsApp dan nama panggilan pelanggan di layar POS dalam waktu 5 detik. Profil pelanggan langsung aktif, poin transaksi pertama langsung masuk, dan pelanggan dapat menerima nota digital via WhatsApp.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah poin belanja bisa digunakan di cabang outlet yang berbeda?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Ya, poin berlaku universal di seluruh cabang yang terhubung dengan akun COOCA Anda. Pelanggan yang berbelanja di Cabang A dapat menukarkan poin diskonnya saat bertransaksi di Cabang B.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Apakah data kontak pelanggan saya aman jika ada staf atau kasir yang mengundurkan diri?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Sangat aman. Database pelanggan tersimpan di cloud terpusat milik perusahaan. Staf kasir hanya bisa melihat nama dan sisa poin saat melayani pembayaran, dan hak akses untuk mengekspor database nomor telepon hanya dimiliki oleh Owner atau Admin yang diberi izin khusus.
                </p>
            </details>

            <details class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                    <span>Bisakah kami mengimpor database kontak pelanggan lama dari file Excel?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                </summary>
                <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    Tentu bisa. COOCA menyediakan template impor Excel untuk memasukkan daftar nama, nomor telepon, alamat, dan saldo poin awal pelanggan lama Anda secara massal dalam hitungan menit.
                </p>
            </details>
        </div>
    </section>

    {{-- 6. TOPICAL CLUSTER --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
            <div>
                <h2 class="text-xs uppercase tracking-widest text-purple-600 dark:text-purple-400 font-semibold mb-1">Modul Terkait</h2>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Pengalaman Pelanggan</p>
            </div>
            <a href="{{ route('public.erp.erp') }}" class="text-xs sm:text-sm font-semibold text-purple-600 dark:text-purple-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                <span>Lihat Seluruh Modul ERP</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('public.erp.pos') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="monitor" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">Point of Sale (POS)</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Identifikasi member dan potong poin diskon langsung di meja kasir.</p>
            </a>

            <a href="{{ route('public.omnichannel.customer') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="users-round" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">Omnichannel Customer</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Konsolidasi identitas pembeli dari marketplace dan WhatsApp.</p>
            </a>

            <a href="{{ route('public.content.creation') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">Content & Promo Broadcast</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Buat materi promosi menarik untuk disebarkan ke segmen member.</p>
            </a>

            <a href="{{ route('public.erp.analytics') }}" class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-purple-500/50 transition-all group">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-purple-600 transition-colors">Analitik Retensi Pelanggan</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pantau rasio repeat order dan nilai Customer Lifetime Value (CLV).</p>
            </a>
        </div>
    </section>

    {{-- 7. BOTTOM CONVERSION CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
        <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                    Bangun Hubungan Kuat dengan Pelanggan Bisnis Anda
                </h2>
                <p class="text-sm sm:text-base text-neutral-400">
                    Mulai kelola database pelanggan secara terpusat dan raih pertumbuhan omzet berkelanjutan melalui program loyalitas yang efektif.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                    <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-semibold text-sm transition-all shadow-md">
                        Coba Demo Modul CRM
                    </a>
                    <a href="{{ route('public.pricing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                        Konsultasi Program Loyalitas
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
