@extends('layouts.public_marketing')

@section('title', 'Panduan Operasional & Dokumentasi SOP Bisnis UMKM | COOCA')
@section('description', 'Panduan langkah demi langkah implementasi sistem COOCA: setup awal gerai, pairing printer Bluetooth thermal 58mm/80mm, impor Excel massal, dan SOP buka-tutup kasir.')
@section('keywords', 'panduan cooca, cara setting printer kasir bluetooth, sop kasir toko, cara impor produk excel, cara stok opname akurat, tutorial pos android')

@push('seo')
<link rel="canonical" href="{{ route('public.resources.guides') }}" />
<meta property="og:title" content="Panduan Operasional & Dokumentasi SOP Bisnis UMKM | COOCA" />
<meta property="og:description" content="Tutorial langkah demi langkah menyiapkan gerai, menghubungkan printer thermal, dan menjalankan SOP kasir profesional." />
<meta property="og:url" content="{{ route('public.resources.guides') }}" />
<meta property="og:type" content="article" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="Panduan Operasional & Dokumentasi SOP Bisnis UMKM | COOCA" />
<meta name="twitter:description" content="Setup kasir kilat, pairing printer Bluetooth, dan SOP buka-tutup shift kasir." />

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "HowTo",
  "name": "Cara Memulai dan Menyiapkan Sistem Kasir COOCA untuk Toko Baru",
  "description": "Panduan 4 langkah menyiapkan sistem operasional dan kasir COOCA mulai dari pendaftaran, impor data produk, hingga menghubungkan printer kasir Bluetooth.",
  "step": [
    {
      "@@type": "HowToStep",
      "position": 1,
      "name": "Registrasi Akun dan Profil Gerai",
      "text": "Daftarkan akun bisnis Anda, masukkan nama usaha, alamat gerai, dan nomor WhatsApp resmi untuk pengiriman struk digital."
    },
    {
      "@@type": "HowToStep",
      "position": 2,
      "name": "Impor Data Produk dan Stok via Excel",
      "text": "Unduh template spreadsheet resmi COOCA, salin daftar barang serta harga jual dan beli (HPP), lalu unggah untuk memuat seluruh katalog produk."
    },
    {
      "@@type": "HowToStep",
      "position": 3,
      "name": "Hubungkan Printer Thermal Struk",
      "text": "Sambungkan printer thermal 58mm atau 80mm via Bluetooth atau kabel USB ke tablet/laptop kasir, lalu lakukan tes cetak struk pertama."
    },
    {
      "@@type": "HowToStep",
      "position": 4,
      "name": "Jalankan Shift Kasir Pertama",
      "text": "Buka shift kasir dengan memasukkan nominal modal awal uang kembalian (float), lalu mulai layani transaksi pelanggan secara cepat."
    }
  ]
}
</script>
@endpush

@section('content')
<div class="relative bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100 overflow-hidden" x-data="{ activeTab: 'onboarding' }">
    <!-- Ambient Gradients -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[460px] bg-gradient-to-b from-indigo-500/10 via-sky-500/5 to-transparent blur-3xl pointer-events-none -z-10"></div>

    <!-- 1. HERO SECTION -->
    <section class="pt-28 pb-16 lg:pt-36 lg:pb-20 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/20 text-sky-700 dark:text-sky-400 text-xs font-semibold uppercase tracking-wider mb-6">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>Dokumentasi Sistem & SOP Operasional</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15] mb-6">
                    Panduan Praktis Membangun Operasional Bisnis yang Tertib
                </h1>

                <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed mb-8">
                    Tidak ada lagi kasir bingung atau salah hitung kembalian. Pelajari dokumentasi teknis, petunjuk pairing printer thermal Bluetooth, alur impor data ribuan produk, dan SOP operasional siap pakai untuk tim gerai Anda.
                </p>

                <!-- Navigation Tabs Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        @click="activeTab = 'onboarding'" 
                        :class="activeTab === 'onboarding' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                        class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        Roadmap Setup Gerai
                    </button>
                    <button 
                        @click="activeTab = 'hardware'" 
                        :class="activeTab === 'hardware' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                        class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        Konfigurasi Printer & Hardware
                    </button>
                    <button 
                        @click="activeTab = 'sop'" 
                        :class="activeTab === 'sop' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                        class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Blueprint SOP Kasir
                    </button>
                    <button 
                        @click="activeTab = 'toolkit'" 
                        :class="activeTab === 'toolkit' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                        class="px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Toolkit & Template Impor
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. SECTION CONTENT WRAPPER -->
    <div class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- TAB 1: ROADMAP SETUP GERAI -->
            <div x-show="activeTab === 'onboarding'" class="space-y-12">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Roadmap 4 Langkah Setup Gerai Baru</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Ikuti tahapan berurutan ini agar sistem operasional toko Anda siap melayani pelanggan dalam hitungan menit.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm relative">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center mb-4">
                            1
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Profil & Identitas Toko</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                            Lengkapi nama gerai, logo bisnis, alamat cabang, dan nomor WhatsApp resmi. Informasi ini akan otomatis tercetak pada header struk kasir Anda.
                        </p>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400">
                            <strong>Lokasi Menu:</strong> Pengaturan &rarr; Profil Usaha & Gerai
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm relative">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center mb-4">
                            2
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Katalog Produk & HPP</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                            Tambahkan nama barang, barcode SKU, harga jual, dan harga modal (HPP). Untuk ratusan item, gunakan fitur Impor Excel massal agar lebih cepat.
                        </p>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400">
                            <strong>Lokasi Menu:</strong> Produk & Inventori &rarr; Impor Excel
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm relative">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center mb-4">
                            3
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Hubungkan Printer Struk</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                            Nyalakan printer Bluetooth atau sambungkan printer kabel USB ke tablet/laptop kasir Anda. Lakukan tes cetak untuk memastikan kertas keluar sempurna.
                        </p>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400">
                            <strong>Lokasi Menu:</strong> Kasir POS &rarr; Pengaturan Printer
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm relative">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center mb-4">
                            4
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Buka Shift & Transaksi</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                            Masukkan nominal uang kembalian di laci kasir (uang modal awal). Kasir kini siap melayani antrean belanja pelanggan pertama Anda.
                        </p>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400">
                            <strong>Lokasi Menu:</strong> Kasir POS &rarr; Buka Shift Kasir
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: HARDWARE & PRINTER -->
            <div x-show="activeTab === 'hardware'" class="space-y-12">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Panduan Konfigurasi Hardware Kasir</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Petunjuk teknis menghubungkan printer thermal, scanner barcode, dan laci uang fisik.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Bluetooth Printer Guide -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-6">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Cara Pairing Printer Bluetooth (58mm / 80mm)</h3>
                        <ol class="space-y-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 list-decimal pl-5 leading-relaxed">
                            <li>Nyalakan printer thermal dan pastikan kertas struk terpasang dengan posisi menghadap ke atas.</li>
                            <li>Buka menu <strong>Bluetooth</strong> pada smartphone atau tablet kasir Anda, lalu aktifkan pencarian perangkat baru.</li>
                            <li>Pilih nama printer Anda (biasanya terdeteksi sebagai <em>RPP02N</em>, <em>MPT-II</em>, atau <em>Bluetooth Printer</em>).</li>
                            <li>Jika meminta PIN sandi pairing, masukkan angka default pabrikan: <code>0000</code> atau <code>1234</code>.</li>
                            <li>Buka aplikasi COOCA di browser, masuk ke menu <strong>Pengaturan Kasir &gt; Printer</strong>, pilih perangkat printer yang telah tersambung, lalu tekan <strong>Tes Cetak Struk</strong>.</li>
                        </ol>
                    </div>

                    <!-- Kitchen LAN Printer Guide -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-6">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Setup Printer Dapur KOT (Kabel LAN / Wi-Fi)</h3>
                        <ol class="space-y-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 list-decimal pl-5 leading-relaxed">
                            <li>Hubungkan kabel LAN Ethernet dari port belakang printer thermal dapur langsung ke router Wi-Fi gerai Anda.</li>
                            <li>Cetak lembar status printer (Self-Test) dengan menahan tombol Feed saat menyalakan printer untuk melihat alamat IP (misal: <code>192.168.1.100</code>).</li>
                            <li>Di dashboard COOCA, masuk ke menu <strong>Pengaturan &gt; Printer Dapur (KOT)</strong>.</li>
                            <li>Masukkan IP Address printer dan centang kategori menu yang ingin otomatis dicetak ke dapur (contoh: kategori Makanan ke Dapur, kategori Minuman ke Bar).</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- TAB 3: SOP KASIR -->
            <div x-show="activeTab === 'sop'" class="space-y-12">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Blueprint Prosedur Operasional Standar (SOP)</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Format SOP standar siap pakai untuk melatih staf kasir baru dan mengeliminasi selisih uang setoran.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- SOP 1 -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 text-xs font-semibold mb-4">
                            SOP Kasir #1
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Alur Tutup Shift (Blind Cash Count)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                            Untuk mencegah manipulasi uang setoran, kasir tidak boleh melihat total penjualan sistem sebelum menghitung uang fisik:
                        </p>
                        <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400 list-disc pl-5">
                            <li>Kasir mengeluarkan seluruh uang tunai dari laci kasir di akhir jam kerja.</li>
                            <li>Hitung lembar uang per pecahan (100rb, 50rb, 20rb, dst) lalu ketik nominal riil ke modal dialog Tutup Shift COOCA.</li>
                            <li>Sistem akan mencocokkan secara otomatis dan mencatat selisih (kurang/lebih) ke dalam laporan audit harian owner.</li>
                        </ul>
                    </div>

                    <!-- SOP 2 -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 text-xs font-semibold mb-4">
                            SOP Inventori #2
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Pencatatan Bahan Basi (Waste & Spoilage)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                            Bahan baku yang basi, tumpah, atau rusak tidak boleh dibuang begitu saja tanpa pencatatan sistem:
                        </p>
                        <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400 list-disc pl-5">
                            <li>Timbang bahan baku yang rusak (contoh: 200 gram daging atau 1 liter susu kedaluwarsa).</li>
                            <li>Buka menu <strong>Inventori &gt; Penyesuaian Stok (Waste/Rusak)</strong> dan masukkan alasan pembuangan.</li>
                            <li>Nilai rupiah dari bahan yang rusak akan otomatis masuk ke akun Biaya Beban Penyusutan pada laporan Laba Rugi.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- TAB 4: TOOLKIT & TEMPLATE IMPOR -->
            <div x-show="activeTab === 'toolkit'" class="space-y-12">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Downloadable Toolkit & Template Resmi</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Unduh lembar kerja spreadsheet dan template siap cetak untuk mempercepat operasional toko Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Template 1: Excel Import -->
                    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Template Impor Excel Produk</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Format baku berisi kolom nama barang, kategori, satuan, harga beli (HPP), dan harga jual untuk migrasi cepat.</p>
                        </div>
                        <a href="{{ route('template.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white font-semibold text-xs text-center transition-all inline-flex items-center justify-center gap-1.5">
                            Buka Katalog Template
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- Template 2: HPP Calculator -->
                    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="14.01"/><line x1="12" y1="14" x2="12" y2="14.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="18" x2="16" y2="18.01"/><line x1="12" y1="18" x2="12" y2="18.01"/><line x1="8" y1="18" x2="8" y2="18.01"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Simulasi Kalkulator HPP</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Hitung harga pokok penjualan multi-bahan dan persentase margin keuntungan sebelum menentukan harga menu.</p>
                        </div>
                        <a href="{{ route('kalkulator.hpp') }}" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs text-center transition-all inline-flex items-center justify-center gap-1.5">
                            Gunakan Kalkulator HPP
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- Template 3: FAQ & Support -->
                    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Pusat Tanya Jawab (FAQ)</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Punya pertanyaan seputar kompatibilitas printer atau cara kerja kasir saat internet padam?</p>
                        </div>
                        <a href="{{ route('public.resources.faq') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white font-semibold text-xs text-center transition-all inline-flex items-center justify-center gap-1.5">
                            Buka Halaman FAQ
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 3. FINAL CONVERSION CTA -->
    <section class="py-16 bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
                Siap Menerapkan Sistem Operasional yang Tertib?
            </h2>
            <p class="text-slate-300 max-w-2xl mx-auto text-base sm:text-lg mb-8 leading-relaxed">
                Terapkan SOP bisnis profesional tanpa ribet. Mulai uji coba gratis sekarang dan rasakan kemudahan mengontrol gerai Anda secara terpusat.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all text-center">
                    Mulai Uji Coba Gratis
                </a>
                <a href="{{ route('public.demo') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 text-slate-200 font-semibold text-sm transition-all text-center">
                    Lihat Demonstrasi Kasir
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
