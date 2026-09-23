@extends('layouts.public_marketing')

@section('title', 'Studi Kasus & Kisah Sukses UMKM Indonesia: Transformasi Bisnis Nyata | COOCA')
@section('description', 'Pelajari bagaimana pelaku UMKM F&B, retail, bengkel, laundry, fashion, dan konveksi berhasil mengeliminasi selisih stok, memangkas biaya operasional, dan melipatgandakan profit bersama COOCA.')
@section('keywords', 'studi kasus umkm, kisah sukses bisnis, efisiensi bisnis umkm, sistem kasir multi cabang, kontrol hpp makanan, aplikasi bengkel motor, software erp indonesia')

@push('seo')
<link rel="canonical" href="{{ route('public.resources.case-studies') }}" />
<meta property="og:title" content="Studi Kasus & Kisah Sukses UMKM Indonesia: Transformasi Bisnis Nyata | COOCA" />
<meta property="og:description" content="Pelajari kisah nyata pemilik bisnis Indonesia mengeliminasi kebocoran stok, memangkas waktu rekap kasir, dan menumbuhkan cabang bersama COOCA." />
<meta property="og:url" content="{{ route('public.resources.case-studies') }}" />
<meta property="og:type" content="article" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="Studi Kasus Transformasi UMKM Indonesia | COOCA" />
<meta name="twitter:description" content="Kisah sukses nyata dari F&B, retail grosir, bengkel motor, hingga laundry mengotomasi operasional harian." />

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Studi Kasus & Kisah Sukses UMKM Indonesia - COOCA",
  "description": "Kumpulan studi kasus implementasi Business Operating System COOCA pada UMKM di berbagai sektor industri di Indonesia.",
  "url": "{{ route('public.resources.case-studies') }}",
  "provider": {
    "@type": "Organization",
    "name": "COOCA",
    "url": "{{ url('/') }}"
  },
  "mainEntity": {
    "@type": "ItemList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Kopi Sudut Santai: Hemat Food Cost 12% dan Kontrol Resep Otomatis"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Toko Kelontong Berkah: Bon Hutang Nol Macet dan Kasir Kilat Barcode"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "Bengkel Motor Perkasa: Tertib SPK Digital & Transparansi Komisi Montir"
      },
      {
        "@type": "ListItem",
        "position": 4,
        "name": "Fresh Laundry Express: Zero Pakaian Tertukar dengan Tracking Barcode"
      },
      {
        "@type": "ListItem",
        "position": 5,
        "name": "Boutique Hijab Syari: Sinkronisasi Stok Multi-Channel Toko Fisik dan Online"
      },
      {
        "@type": "ListItem",
        "position": 6,
        "name": "Konveksi Maju Bersama: Presisi HPP Produksi dan Margin Terkunci"
      }
    ]
  }
}
</script>
@endpush

@section('content')
<div class="relative bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100 overflow-hidden" x-data="{ activeFilter: 'all' }">
    <!-- Background Ambient Gradients -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[480px] bg-gradient-to-b from-indigo-500/10 via-emerald-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    <!-- 1. HERO SECTION -->
    <section class="pt-28 pb-16 lg:pt-36 lg:pb-20 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-6">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"></circle>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                    </svg>
                    <span>Studi Kasus & Transformasi Bisnis Nyata</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15] mb-6">
                    Kisah Nyata UMKM yang Berhasil Menutup Kebocoran dan Naik Kelas
                </h1>

                <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed mb-8">
                    Bukan sekadar teori manajemen. Pelajari bagaimana pemilik bisnis lokal dari kedai kopi, minimarket, bengkel, hingga pabrik konveksi menata ulang operasional harian, menghentikan selisih kasir, dan mengelola banyak cabang dengan tenang.
                </p>

                <!-- Verified Impact Metrics Bento Strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">12.4%</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Rata-rata Hemat Food Cost</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">5 Menit</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Rekap Tutup Buku Tiap Shift</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">99.8%</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Presisi Stok & Gudang</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <div class="text-2xl font-black text-cyan-600 dark:text-cyan-400 tracking-tight">0%</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Nota Kasbon Tercecer</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. FILTER & CATALOG SECTION -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filter Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 pb-6 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Daftar Studi Kasus Per Industri</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih kategori bisnis untuk melihat solusi spesifik yang relevan dengan usaha Anda.</p>
                </div>

                <!-- Filter Buttons -->
                <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-200/60 dark:bg-slate-800/80 rounded-xl">
                    <button 
                        @click="activeFilter = 'all'" 
                        :class="activeFilter === 'all' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        Semua Industri
                    </button>
                    <button 
                        @click="activeFilter = 'fnb'" 
                        :class="activeFilter === 'fnb' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        F&B & Resto
                    </button>
                    <button 
                        @click="activeFilter = 'retail'" 
                        :class="activeFilter === 'retail' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        Retail & Grosir
                    </button>
                    <button 
                        @click="activeFilter = 'workshop'" 
                        :class="activeFilter === 'workshop' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        Bengkel & Servis
                    </button>
                    <button 
                        @click="activeFilter = 'services'" 
                        :class="activeFilter === 'services' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        Laundry & Jasa
                    </button>
                    <button 
                        @click="activeFilter = 'fashion'" 
                        :class="activeFilter === 'fashion' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                        Fashion & Konveksi
                    </button>
                </div>
            </div>

            <!-- Case Studies Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Case 1: Kopi Sudut Santai -->
                <div x-show="activeFilter === 'all' || activeFilter === 'fnb'" 
                     class="flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-400 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                                <line x1="6" y1="1" x2="6" y2="4"></line>
                                <line x1="10" y1="1" x2="10" y2="4"></line>
                                <line x1="14" y1="1" x2="14" y2="4"></line>
                            </svg>
                            F&B / Kedai Kopi 3 Cabang
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            Hemat 12.4% Food Cost
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Kopi Sudut Santai: Resep Otomatis Memotong Bahan Basi & Selisih Susu
                    </h3>

                    <!-- Story Breakdown -->
                    <div class="space-y-3 my-4 text-xs text-slate-600 dark:text-slate-300 flex-1">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan Awal:</span>
                            Takaran susu fresh dan sirup berbeda di tiap barista. Setiap akhir bulan terjadi selisih hingga 45 liter susu tanpa ada penjelasan transaksi yang jelas.
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Solusi COOCA:</span>
                            Penerapan Bill of Materials (BOM) otomatis per cangkir pesanan kasir, integrasi printer dapur KOT, dan sinkronisasi stok bahan baku antar 3 outlet.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Modul Terkait:</span>
                        <a href="{{ route('public.solutions.fnb') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            Sistem F&B COOCA
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Case 2: Toko Kelontong Berkah -->
                <div x-show="activeFilter === 'all' || activeFilter === 'retail'" 
                     class="flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-700 dark:text-blue-400 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                                <path d="M2 7h20"></path>
                            </svg>
                            Retail / Toko Kelontong & Grosir
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            Piutang Macet Turun 85%
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Toko Kelontong Berkah: Bon Hutang Nol Macet dengan Pengingat Otomatis
                    </h3>

                    <!-- Story Breakdown -->
                    <div class="space-y-3 my-4 text-xs text-slate-600 dark:text-slate-300 flex-1">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan Awal:</span>
                            Kasbon tetangga dan warung binaan dicatat di buku tulis. Buku sering terselip atau halaman robek, menyebabkan kerugian piutang jutaan rupiah setiap tahun.
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Solusi COOCA:</span>
                            Manajemen piutang digital dengan batas pagu hutang otomatis, cetak nota barcode kilat, serta rekap penagihan jatuh tempo via WhatsApp otomatis.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Modul Terkait:</span>
                        <a href="{{ route('public.solutions.retail') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            Sistem Retail COOCA
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Case 3: Bengkel Motor Perkasa -->
                <div x-show="activeFilter === 'all' || activeFilter === 'workshop'" 
                     class="flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-violet-500/10 text-violet-700 dark:text-violet-400 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                            </svg>
                            Otomotif / Bengkel Motor & Sparepart
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            Servis Naik 35%
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Bengkel Motor Perkasa: Tertib SPK Digital & Transparansi Komisi Montir
                    </h3>

                    <!-- Story Breakdown -->
                    <div class="space-y-3 my-4 text-xs text-slate-600 dark:text-slate-300 flex-1">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan Awal:</span>
                            Oli dan busi sering keluar tanpa nota pembayaran. Perhitungan upah bagi hasil 6 mekanik sering memicu perselisihan karena catatan pengerjaan hilang.
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Solusi COOCA:</span>
                            Alur digital Surat Perintah Kerja (SPK) terintegrasi dengan nomor polisi, lock sparepart sesuai job card, dan kalkulasi persentase jasa mekanik otomatis.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Modul Terkait:</span>
                        <a href="{{ route('public.solutions.workshop') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            Sistem Bengkel COOCA
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Case 4: Fresh Laundry Express -->
                <div x-show="activeFilter === 'all' || activeFilter === 'services'" 
                     class="flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                            </svg>
                            Jasa / Laundry Kiloan & Satuan
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            0% Pakaian Tertukar
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Fresh Laundry Express: Zero Baju Tertukar dengan Pelacakan QR Rak
                    </h3>

                    <!-- Story Breakdown -->
                    <div class="space-y-3 my-4 text-xs text-slate-600 dark:text-slate-300 flex-1">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan Awal:</span>
                            Pelanggan komplain baju hilang saat peak season (musim hujan). Nota bon kertas sering luntur terkena air detergen dan kasir salah memberi kantong pakaian.
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Solusi COOCA:</span>
                            Cetak label barcode anti-air per keranjang, penomoran rak digital, dan notifikasi WhatsApp otomatis saat status pengerjaan siap diambil.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Modul Terkait:</span>
                        <a href="{{ route('public.solutions.services') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            Sistem Jasa COOCA
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Case 5: Boutique Hijab Syari -->
                <div x-show="activeFilter === 'all' || activeFilter === 'fashion'" 
                     class="flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-pink-500/10 text-pink-700 dark:text-pink-400 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path>
                            </svg>
                            Fashion / Butik & Toko Pakaian
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            Stok Multi-Channel Sinkron
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Boutique Hijab Syari: Stok Live TikTok & Toko Fisik Terkunci Akurat
                    </h3>

                    <!-- Story Breakdown -->
                    <div class="space-y-3 my-4 text-xs text-slate-600 dark:text-slate-300 flex-1">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan Awal:</span>
                            Saat live streaming malam terjual 50 gamis, namun barang yang sama di etalase toko offline masih dibeli pelanggan siang harinya, berujung penalti pembatalan pesanan.
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Solusi COOCA:</span>
                            Sentralisasi inventori multi-gudang dan sistem variasi warna/ukuran SKU terpadu dengan pembaruan stok real-time antar kanal jualan.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Modul Terkait:</span>
                        <a href="{{ route('public.solutions.retail') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            Manajemen Inventori
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- Case 6: Konveksi Maju Bersama -->
                <div x-show="activeFilter === 'all' || activeFilter === 'fashion'" 
                     class="flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm hover:shadow-md hover:border-indigo-400/50 dark:hover:border-indigo-500/50 transition-all duration-300">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path>
                            </svg>
                            Manufaktur / Konveksi & Sablon
                        </span>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md border border-emerald-500/20">
                            Margin Bersih Terkunci 22%
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                        Konveksi Maju Bersama: Presisi HPP Produksi Seragam Sebelum Tender
                    </h3>

                    <!-- Story Breakdown -->
                    <div class="space-y-3 my-4 text-xs text-slate-600 dark:text-slate-300 flex-1">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="font-bold text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan Awal:</span>
                            Menentukan harga tender berdasarkan perkiraan kasar. Saat harga bahan kain katun naik di tengah jalan, proyek tender seragam berakhir impas bahkan nombok.
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block mb-0.5">Solusi COOCA:</span>
                            Kalkulator estimasi HPP multi-bahan (kain, kancing, benang, sablon) dan tracking pembayaran uang muka (DP) bertahap terintegrasi invoice resmi.
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-150 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Tools Terkait:</span>
                        <a href="{{ route('kalkulator.hpp') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            Kalkulator HPP Produk
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. BEFORE VS AFTER COMPARISON MATRIX -->
    <section class="py-16 bg-white dark:bg-slate-900/50 border-y border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-12">
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 tracking-wider uppercase">Matriks Transformasi</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                    Operasional Sebelum vs Sesudah Menggunakan COOCA
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                    Perubahan langsung yang dirasakan oleh pemilik bisnis dan tim kasir setelah beralih ke satu ekosistem operasional terpadu.
                </p>
            </div>

            <!-- Bento Comparison Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-4 px-4 w-1/4">Aspek Operasional</th>
                            <th class="py-4 px-4 w-3/8 text-rose-600 dark:text-rose-400 bg-rose-500/5 rounded-t-xl">Sebelum Pakai COOCA</th>
                            <th class="py-4 px-4 w-3/8 text-emerald-600 dark:text-emerald-400 bg-emerald-500/5 rounded-t-xl">Sesudah Pakai COOCA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">Tutup Kasir Harian</td>
                            <td class="py-4 px-4 text-slate-600 dark:text-slate-400 bg-rose-500/5">Manual hitung nota kertas 2–3 jam tiap malam, rawan selisih uang kas fisik.</td>
                            <td class="py-4 px-4 text-slate-800 dark:text-slate-200 font-medium bg-emerald-500/5">Otomatis balance dalam 5 menit, laporan kasir langsung terkirim ke WhatsApp owner.</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">Kontrol Stok & Bahan Baku</td>
                            <td class="py-4 px-4 text-slate-600 dark:text-slate-400 bg-rose-500/5">Stok hanya dihitung saat opname bulanan, selisih bahan basi sering tidak terlacak.</td>
                            <td class="py-4 px-4 text-slate-800 dark:text-slate-200 font-medium bg-emerald-500/5">Resep BOM terpotong otomatis tiap transaksi kasir, alert stok menipis realtime.</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">Piutang & Kasbon Pelanggan</td>
                            <td class="py-4 px-4 text-slate-600 dark:text-slate-400 bg-rose-500/5">Dicatat di bon kertas terselip, sering lupa ditagih sampai bertahun-tahun.</td>
                            <td class="py-4 px-4 text-slate-800 dark:text-slate-200 font-medium bg-emerald-500/5">Plafon kredit terkunci di sistem, pengingat jatuh tempo otomatis via WhatsApp.</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">Pengawasan Multi-Cabang</td>
                            <td class="py-4 px-4 text-slate-600 dark:text-slate-400 bg-rose-500/5">Owner harus datang keliling fisik ke tiap cabang untuk cek setoran tunai.</td>
                            <td class="py-4 px-4 text-slate-800 dark:text-slate-200 font-medium bg-emerald-500/5">Dashboard sentral dari HP, omzet dan laba per cabang terlihat detik demi detik.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- 4. FAQ STUDI KASUS -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Tanya Jawab Implementasi</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">
                    Pertanyaan Seputar Penerapan COOCA di Lapangan
                </h2>
            </div>

            <div class="space-y-4" x-data="{ openFaq: null }">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                    <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full py-4 px-5 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm">
                        <span>Berapa lama proses implementasi dari sistem manual ke COOCA?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 1 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 pt-3">
                        Rata-rata UMKM hanya membutuhkan 1 hingga 3 hari kerja. Anda dapat langsung mengimpor data produk dan pelanggan menggunakan template Excel yang sudah kami sediakan, lalu kasir dapat langsung digunakan pada tablet atau komputer apa pun tanpa instalasi server lokal rumit.
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                    <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full py-4 px-5 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm">
                        <span>Apakah staf dan kasir yang belum terbiasa komputer bisa mengoperasikannya?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 2 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 pt-3">
                        Sangat bisa. Antarmuka kasir COOCA dirancang dengan prinsip Apple Human Interface Guidelines: tombol sentuh besar (min 44px), visual foto menu/produk yang jelas, dan alur pembayaran satu sentuhan. Staf baru rata-rata dapat melayani pembeli secara mandiri dalam waktu latihan kurang dari 30 menit.
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                    <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full py-4 px-5 text-left flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm">
                        <span>Bagaimana jika koneksi internet di toko fisik tiba-tiba mati?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === 3 ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 pt-3">
                        Modul Kasir POS COOCA mendukung mode offline fallback. Kasir tetap dapat memindai barang, melayani antrean, dan mencetak struk thermal. Ketika koneksi internet kembali menyala, seluruh data transaksi akan tersinkronisasi otomatis ke server pusat tanpa risiko data ganda.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. FINAL CONVERSION CTA -->
    <section class="py-16 bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
                Siap Menjadikan Usaha Anda Kisah Sukses Berikutnya?
            </h2>
            <p class="text-slate-300 max-w-2xl mx-auto text-base sm:text-lg mb-8 leading-relaxed">
                Tinggalkan pencatatan manual yang menguras tenaga dan waktu tidur Anda. Uji coba COOCA sekarang dan rasakan kemudahan mengontrol bisnis dari genggaman Anda.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all text-center">
                    Mulai Uji Coba Gratis
                </a>
                <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-slate-200 font-semibold text-sm transition-all text-center">
                    Lihat Demonstrasi Interaktif
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
