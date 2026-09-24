@extends('layouts.public_marketing')

@section('title', 'Panduan Operasional & Dokumentasi SOP Bisnis UMKM | COOCA')
@section('description', 'Panduan langkah demi langkah implementasi sistem COOCA: setup awal gerai, pairing printer Bluetooth thermal 58mm/80mm, impor Excel massal, dan SOP buka-tutup kasir.')
@section('og_title', 'Panduan Operasional & Dokumentasi SOP Bisnis UMKM | COOCA')
@section('og_description', 'Tutorial langkah demi langkah menyiapkan gerai, menghubungkan printer thermal, dan menjalankan SOP kasir profesional.')
@section('canonical', route('public.resources.guides'))
@section('og_type', 'article')
@section('keywords', 'panduan cooca, cara setting printer kasir bluetooth, sop kasir toko, cara impor produk excel, cara stok opname akurat, tutorial pos android')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "HowTo",
  "name": "Cara Memulai dan Menyiapkan Sistem Kasir COOCA untuk Toko Baru",
  "description": "Panduan 4 langkah menyiapkan sistem operasional dan kasir COOCA mulai dari pendaftaran, impor data produk, hingga menghubungkan printer kasir Bluetooth.",
  "step": [
    {
      "@type": "HowToStep",
      "position": 1,
      "name": "Registrasi Akun dan Profil Gerai",
      "text": "Daftarkan akun bisnis Anda, masukkan nama usaha, alamat gerai, dan nomor WhatsApp resmi untuk pengiriman struk digital."
    },
    {
      "@type": "HowToStep",
      "position": 2,
      "name": "Impor Data Produk dan Stok via Excel",
      "text": "Unduh template spreadsheet resmi COOCA, salin daftar barang serta harga jual dan beli (HPP), lalu unggah untuk memuat seluruh katalog produk."
    },
    {
      "@type": "HowToStep",
      "position": 3,
      "name": "Hubungkan Printer Thermal Struk",
      "text": "Sambungkan printer thermal 58mm atau 80mm via Bluetooth atau kabel USB ke tablet/laptop kasir, lalu lakukan tes cetak struk pertama."
    },
    {
      "@type": "HowToStep",
      "position": 4,
      "name": "Jalankan Shift Kasir Pertama",
      "text": "Buka shift kasir dengan memasukkan nominal modal awal uang kembalian (float), lalu mulai layani transaksi pelanggan secara cepat."
    }
  ]
}
</script>
@endpush

@section('content')
<div x-data="{ 
    activeTab: 'onboarding',
    showSopModal: false,
    activeSop: 'shift',
    openSopModal(sopType) {
        this.activeSop = sopType || 'shift';
        this.showSopModal = true;
        this.refreshIcons();
    },
    closeSopModal() {
        this.showSopModal = false;
    },
    refreshIcons() {
        this.$nextTick(() => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }
}" x-init="refreshIcons()"
class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION (Apple Bento Modern Dark Style without Breadcrumb) ═══ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section
        class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
        {{-- Dual Ambient Glows --}}
        <div
            class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
        </div>
        <div
            class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
        </div>

        <div
            class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-10 sm:pb-20 lg:py-14">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full">
                
                <!-- Left Column: Headline, Value Proposition & Actions -->
                <div class="lg:col-span-6 space-y-6 text-left flex flex-col items-start w-full">
                    <div class="space-y-3 w-full">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.06] border border-white/10 text-xs sm:text-[13px] font-semibold text-[#00C4D8] backdrop-blur-md mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <span>Panduan Operasional &amp; SOP Gerai</span>
                        </div>

                        <h1
                            class="text-2.5xl xs:text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-black tracking-tight text-white leading-[1.15] text-balance break-words text-left">
                            Membangun Operasional Gerai yang <span
                                class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Rapi
                                &amp; Tertib</span>
                        </h1>
                    </div>

                    <p
                        class="text-sm sm:text-base lg:text-lg text-slate-300 leading-relaxed font-normal text-pretty text-left max-w-xl">
                        Petunjuk langsung untuk pemilik usaha dan staf kasir. Dari cara mudah menyambungkan printer
                        thermal Bluetooth, mengimpor ribuan produk dari Excel, hingga SOP tutup kasir tanpa selisih uang
                        setoran.
                    </p>

                    <!-- Direct Action Buttons -->
                    <div
                        class="flex flex-col sm:flex-row items-stretch sm:items-center justify-start gap-3.5 pt-2 w-full sm:w-auto">
                        <a href="#panduan-setup"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200 min-h-[48px]">
                            <span>Mulai Setup 4 Langkah</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ route('template.index') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all min-h-[48px]">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Unduh Template Excel</span>
                        </a>
                    </div>

                    <!-- Trust Commitments for UMKM -->
                    <div
                        class="flex flex-wrap items-center gap-x-6 gap-y-2.5 pt-4 border-t border-white/10 text-xs sm:text-sm text-slate-300 font-medium w-full">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>Siap Pakai dalam 15 Menit</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>Kompatibel Printer Universal</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div
                                class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </div>
                            <span>Dokumentasi SOP Praktis</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Real Thermal Receipt & Step Checklist Bento Preview -->
                <div class="lg:col-span-6 relative w-full mt-4 lg:mt-0">
                    <div
                        class="relative rounded-2xl sm:rounded-3xl bg-gradient-to-b from-[#0E1E45]/90 to-[#0A122C]/90 p-4 sm:p-6 shadow-2xl border border-white/15 ring-1 ring-white/10 backdrop-blur-xl text-white overflow-hidden space-y-4">
                        {{-- Spotlight Decoration --}}
                        <div
                            class="absolute -top-24 -right-24 w-48 h-48 bg-[#007AFF]/25 rounded-full blur-3xl pointer-events-none">
                        </div>
                        
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div>
                                <div class="text-xs font-bold text-white">Alur Pelatihan Kasir</div>
                                <div class="text-[11px] text-slate-400">Contoh Hasil Cetak Struk Sempurna</div>
                            </div>
                            <span
                                class="text-[11px] font-bold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/25 px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                Standar SOP
                            </span>
                        </div>

                        <!-- Mini Thermal Struk Simulation -->
                        <div
                            class="p-4 rounded-2xl bg-[#060B1E]/90 border border-white/10 font-mono text-xs space-y-2 text-slate-200">
                            <div class="text-center pb-2 border-b border-dashed border-white/20">
                                <div class="font-bold text-sm text-white">TOKO BERKAH NUSANTARA</div>
                                <div class="text-[11px] text-slate-400">Jl. Pahlawan No. 24, Yogyakarta</div>
                                <div class="text-[10px] text-slate-400">WA: 0812-3456-7890 &bull; Kasir: Budi</div>
                            </div>

                            <div class="space-y-1.5 py-1 text-[11px]">
                                <div class="flex justify-between">
                                    <span>2x Kopi Susu Gula Aren</span>
                                    <span class="tabular-nums text-white">Rp 36.000</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>1x Roti Bakar Cokelat</span>
                                    <span class="tabular-nums text-white">Rp 15.000</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-dashed border-white/20 space-y-1 text-[11px]">
                                <div class="flex justify-between font-bold text-white">
                                    <span>TOTAL TRANSAKSI</span>
                                    <span class="tabular-nums text-[#00C4D8]">Rp 51.000</span>
                                </div>
                                <div class="flex justify-between text-slate-400">
                                    <span>Tunai Diterima</span>
                                    <span class="tabular-nums">Rp 100.000</span>
                                </div>
                                <div class="flex justify-between text-slate-400">
                                    <span>Kembalian</span>
                                    <span class="tabular-nums font-semibold text-emerald-400">Rp 49.000</span>
                                </div>
                            </div>

                            <div class="text-center pt-2 text-[10px] text-slate-400">
                                Terima kasih atas kunjungan Anda
                            </div>
                        </div>

                        <!-- Footer Step Helper -->
                        <div class="pt-1 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Printer: Bluetooth 58mm &bull; 80mm Auto-Cutter</span>
                            <button @click="openSopModal('shift')" class="font-bold text-[#00C4D8] hover:underline">
                                Lihat SOP Kasir &rarr;
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. TABS NAVIGASI 4 MODE PANDUAN ══════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div id="panduan-setup" class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-10">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
            <div class="max-w-2xl space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    PILIHAN MODUL PANDUAN
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Dokumentasi Lengkap Sesuai Tahapan Kebutuhan
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    Pilih panduan konfigurasi yang ingin Anda pelajari atau bagikan kepada staf operasional gerai.
                </p>
            </div>

            <!-- Segmented Control Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 p-1.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[16px] shadow-sm">
                <button 
                    @click="activeTab = 'onboarding'; refreshIcons();" 
                    :class="activeTab === 'onboarding' ? 'bg-[#007AFF] text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all flex items-center gap-2">
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                    <span>Setup 4 Langkah</span>
                </button>
                <button 
                    @click="activeTab = 'hardware'; refreshIcons();" 
                    :class="activeTab === 'hardware' ? 'bg-[#007AFF] text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all flex items-center gap-2">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Printer &amp; Hardware</span>
                </button>
                <button 
                    @click="activeTab = 'sop'; refreshIcons();" 
                    :class="activeTab === 'sop' ? 'bg-[#007AFF] text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                    <span>Blueprint SOP Kasir</span>
                </button>
                <button 
                    @click="activeTab = 'toolkit'; refreshIcons();" 
                    :class="activeTab === 'toolkit' ? 'bg-[#007AFF] text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Template Excel</span>
                </button>
            </div>
        </div>

        <!-- ═══ TAB 1: ROADMAP SETUP 4 LANGKAH ═══ -->
        <div x-show="activeTab === 'onboarding'" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Step 1 -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center font-mono">
                            01
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Profil &amp; Identitas Usaha</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Lengkapi nama gerai, alamat toko, dan nomor WhatsApp resmi. Informasi ini akan otomatis tercetak di header struk kasir Anda.
                        </p>
                    </div>
                    <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] text-xs text-slate-600 dark:text-slate-400">
                        <strong>Menu:</strong> Pengaturan &rarr; Profil Gerai
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center font-mono">
                            02
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Katalog Produk &amp; HPP</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Masukkan nama barang, barcode SKU, harga jual, dan modal beli (HPP). Untuk ratusan barang, gunakan impor Excel massal 1 kali klik.
                        </p>
                    </div>
                    <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] text-xs text-slate-600 dark:text-slate-400">
                        <strong>Menu:</strong> Produk &rarr; Impor Excel
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs flex items-center justify-center font-mono">
                            03
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Hubungkan Printer Struk</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Nyalakan printer Bluetooth atau pasang kabel USB ke tablet/laptop kasir Anda. Lakukan tes cetak untuk memastikan kertas keluar rapi.
                        </p>
                    </div>
                    <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] text-xs text-slate-600 dark:text-slate-400">
                        <strong>Menu:</strong> Kasir POS &rarr; Printer
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center font-mono">
                            04
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Buka Shift &amp; Transaksi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Ketik nominal uang kembalian modal awal di laci (float kas). Kasir langsung siap melayani antrean belanja pembeli pertama Anda.
                        </p>
                    </div>
                    <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12] text-xs text-emerald-700 dark:text-emerald-400 font-semibold">
                        <strong>Menu:</strong> Kasir POS &rarr; Buka Shift
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ TAB 2: HARDWARE & PRINTER ═══ -->
        <div x-show="activeTab === 'hardware'" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Bluetooth Printer Guide -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] p-7 border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cara Menyambungkan Printer Bluetooth (58mm / 80mm)</h3>
                    <ol class="space-y-3 text-sm text-slate-600 dark:text-slate-300 list-decimal pl-5 leading-relaxed">
                        <li>Nyalakan printer thermal dan pastikan kertas struk terpasang dengan posisi gulungan menghadap ke atas.</li>
                        <li>Buka menu <strong>Bluetooth</strong> pada smartphone atau tablet kasir Anda, lalu aktifkan pencarian perangkat baru.</li>
                        <li>Pilih nama printer Anda (biasanya terdeteksi sebagai <em>RPP02N</em>, <em>MPT-II</em>, atau <em>Bluetooth Printer</em>).</li>
                        <li>Jika meminta PIN pairing, masukkan angka default pabrikan: <code>0000</code> atau <code>1234</code>.</li>
                        <li>Buka COOCA di browser, masuk ke menu <strong>Pengaturan Kasir &gt; Printer</strong>, pilih perangkat yang telah tersambung, lalu tekan <strong>Tes Cetak Struk</strong>.</li>
                    </ol>
                </div>

                <!-- Kitchen LAN Printer Guide -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] p-7 border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4">
                    <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="network" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Setup Printer Dapur KOT (Kabel LAN / Wi-Fi)</h3>
                    <ol class="space-y-3 text-sm text-slate-600 dark:text-slate-300 list-decimal pl-5 leading-relaxed">
                        <li>Hubungkan kabel LAN Ethernet dari port belakang printer dapur langsung ke router Wi-Fi gerai Anda.</li>
                        <li>Cetak lembar status printer (Self-Test) dengan menahan tombol Feed saat menyalakan printer untuk melihat alamat IP (misal: <code>192.168.1.100</code>).</li>
                        <li>Di dashboard COOCA, masuk ke menu <strong>Pengaturan &gt; Printer Dapur (KOT)</strong>.</li>
                        <li>Masukkan IP Address printer dan centang kategori menu yang ingin otomatis dicetak ke dapur (kategori Makanan ke Dapur, Minuman ke Bar).</li>
                    </ol>
                </div>

            </div>
        </div>

        <!-- ═══ TAB 3: SOP KASIR & LACI UANG ═══ -->
        <div x-show="activeTab === 'sop'" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- SOP 1: Blind Cash Count -->
                <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 px-2.5 py-1 rounded-[8px]">
                            SOP Kasir #1
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Alur Tutup Shift (Blind Cash Count)</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Mencegah kecurangan atau manipulasi uang setoran. Kasir tidak diperlihatkan total angka penjualan sistem sebelum menghitung uang fisik di laci:
                        </p>
                        <ul class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 list-disc pl-5">
                            <li>Keluarkan seluruh uang tunai dari laci kasir pada akhir jam kerja shift.</li>
                            <li>Hitung lembar uang per pecahan (100rb, 50rb, 20rb, dst) lalu ketik nominal riil ke dialog Tutup Shift.</li>
                            <li>Sistem otomatis mencocokkan selisih (kurang/lebih) dan langsung mengirimkan laporan ke WhatsApp pemilik.</li>
                        </ul>
                    </div>
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button @click="openSopModal('shift')" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Buka Template Checklist Tutup Shift</span>
                        </button>
                    </div>
                </div>

                <!-- SOP 2: Waste & Spoilage -->
                <div class="p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-[8px]">
                            SOP Inventori #2
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pencatatan Bahan Basi (Waste &amp; Rusak)</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Bahan baku yang tumpah, basi, atau rusak tidak boleh dibuang tanpa tercatat ke sistem, agar persentase HPP tetap akurat:
                        </p>
                        <ul class="space-y-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 list-disc pl-5">
                            <li>Timbang berat bahan yang rusak (contoh: 300 gram daging atau 1 liter susu kedaluwarsa).</li>
                            <li>Buka menu <strong>Inventori &gt; Penyesuaian Stok (Waste/Rusak)</strong> dan ketik alasan pembuangan.</li>
                            <li>Nilai rupiah bahan basi otomatis dialokasikan ke akun Biaya Beban Penyusutan pada laporan Laba Rugi.</li>
                        </ul>
                    </div>
                    <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button @click="openSopModal('waste')" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Buka Template Formulir Bahan Rusak</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ TAB 4: TOOLKIT & TEMPLATE EXCEL ═══ -->
        <div x-show="activeTab === 'toolkit'" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Template 1: Excel Import -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Template Impor Excel Produk</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Format baku berisi kolom nama barang, kategori, satuan, harga beli (HPP), dan harga jual untuk migrasi cepat ribuan item.
                        </p>
                    </div>
                    <a href="{{ route('template.index') }}" class="h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Katalog Template</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Template 2: HPP Calculator -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Kalkulator Simulasi HPP</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Hitung harga modal resep multi-bahan dan margin keuntungan sebelum menetapkan harga jual di daftar menu gerai.
                        </p>
                    </div>
                    <a href="{{ route('kalkulator.hpp') }}" class="h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Gunakan Kalkulator HPP</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Template 3: FAQ -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pusat Tanya Jawab (FAQ)</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Punya pertanyaan seputar kompatibilitas printer thermal atau cara kerja kasir saat internet toko terputus?
                        </p>
                    </div>
                    <a href="{{ route('public.resources.faq') }}" class="h-11 px-4 rounded-[12px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Halaman FAQ</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 3. CONVERSION CTA SECTION ════════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
        <div class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-center space-y-6 shadow-sm">
            <div class="max-w-2xl mx-auto space-y-3">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    OPERASIONAL TERTIB &amp; PROFESIONAL
                </div>
                <h3 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Siap Menerapkan Sistem Operasional yang Rapi?
                </h3>
                <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                    Terapkan SOP bisnis profesional tanpa proses rumit. Mulai uji coba gratis sekarang dan rasakan kemudahan mengontrol gerai secara terpusat.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-2">
                <a href="{{ route('register') }}"
                    class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 active:scale-[0.98] transition-all shadow-sm">
                    <span>Mulai Uji Coba Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('public.demo') }}"
                    class="h-12 px-7 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] text-slate-900 dark:text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                    <i data-lucide="play" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                    <span>Lihat Demonstrasi Kasir</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 4. MODAL SHEET: Detail SOP Siap Cetak ════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div x-show="showSopModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-sop-title" role="dialog" aria-modal="true">
        
        <div class="fixed inset-0 bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-opacity"
            @click="closeSopModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-8 space-y-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div>
                        <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">DOKUMENTASI STANDAR OPERASIONAL</div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-sop-title">
                            Lembar Petunjuk SOP Kasir
                        </h3>
                    </div>
                    <button @click="closeSopModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#2C2C2E] text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- SOP Shift Details -->
                <div x-show="activeSop === 'shift'" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                    <div class="font-bold text-base text-slate-900 dark:text-white">Checklist SOP Tutup Shift Kasir (Blind Cash Count)</div>
                    <p class="text-sm leading-relaxed">Gunakan prosedur ini untuk melatih staf kasir agar rekonsiliasi uang setoran setiap malam selalu tertib:</p>
                    
                    <div class="space-y-2 pt-1">
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
                            <span>Keluarkan seluruh uang fisik dari laci kasir dan pisahkan antara uang kertas dengan uang logam.</span>
                        </div>
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
                            <span>Kelompokkan uang kertas per pecahan: Rp 100.000, Rp 50.000, Rp 20.000, Rp 10.000, Rp 5.000, Rp 2.000.</span>
                        </div>
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
                            <span>Buka menu <strong>Kasir &gt; Tutup Shift</strong> di COOCA, masukkan jumlah lembar uang per pecahan.</span>
                        </div>
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">4</span>
                            <span>Tekan tombol <strong>Selesai &amp; Cetak Rekap</strong>. Jika terdapat selisih uang, tulis catatan alasan pada kolom keterangan.</span>
                        </div>
                    </div>
                </div>

                <!-- SOP Waste Details -->
                <div x-show="activeSop === 'waste'" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                    <div class="font-bold text-base text-slate-900 dark:text-white">Checklist SOP Pembuangan Bahan Basi &amp; Rusak (Waste)</div>
                    <p class="text-sm leading-relaxed">Prosedur wajib saat terjadi bahan baku tumpah, basi, atau kedaluwarsa di dapur:</p>
                    
                    <div class="space-y-2 pt-1">
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</span>
                            <span>Timbang bahan baku yang rusak sebelum dibuang (misal: 250 gram daging atau 1 cup saus).</span>
                        </div>
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</span>
                            <span>Foto bahan baku yang rusak sebagai dokumentasi bukti audit untuk supervisor atau pemilik toko.</span>
                        </div>
                        <div class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</span>
                            <span>Buka menu <strong>Inventori &gt; Penyesuaian Stok</strong>, pilih opsi <em>Waste / Rusak</em>, dan masukkan berat riil.</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Dapat dicetak sebagai panduan kerja staf</span>
                    <button @click="closeSopModal()" class="h-10 px-5 rounded-[12px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-semibold">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
