@extends('layouts.public_marketing')

@section('title', 'Pusat Bantuan & Tanya Jawab (FAQ) Lengkap: Solusi Kasir & Stok | COOCA')
@section('description', 'Temukan jawaban lengkap seputar skema lisensi gratis, kompatibilitas printer thermal, keamanan data cloud, mode kasir offline, dan panduan migrasi data bisnis ke COOCA.')
@section('og_title', 'Pusat Bantuan & Tanya Jawab (FAQ) Lengkap | COOCA')
@section('og_description', 'Pertanyaan yang sering diajukan seputar operasional, printer thermal, keamanan tenant, dan fitur kasir offline COOCA.')
@section('canonical', route('public.resources.faq'))
@section('og_type', 'website')
@section('keywords', 'faq cooca, tanya jawab aplikasi kasir, cara setting printer thermal bluetooth, aplikasi kasir offline, keamanan data erp umkm, cara impor data excel ke kasir')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Apakah modul operasional esensial COOCA benar-benar gratis?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. COOCA menyediakan modul esensial tanpa biaya bulanan yang mencakup Kasir POS, Manajemen Produk dasar, dan Laporan Rekap Penjualan Harian untuk membantu UMKM Indonesia memulai digitalisasi tanpa beban modal di awal."
      }
    },
    {
      "@type": "Question",
      "name": "Perangkat apa saja yang didukung oleh aplikasi kasir COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA berjalan secara modern berbasis web responsif pada smartphone Android, iPhone, tablet iPad/Android, laptop Windows/macOS, dan komputer desktop PC kasir all-in-one tanpa perlu instalasi aplikasi rumit."
      }
    },
    {
      "@type": "Question",
      "name": "Printer kasir jenis apa saja yang bisa digunakan bersama COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA mendukung hampir semua printer thermal mini standar pasar dengan koneksi Bluetooth (58mm dan 80mm), printer thermal LAN/Ethernet untuk pesanan dapur (Kitchen Order Ticket), serta printer kabel USB untuk kasir komputer PC."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana keamanan data transaksi dan keuangan bisnis saya?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Data Anda terisolasi secara ketat dalam arsitektur multi-tenant. Seluruh transfer data dienkripsi dengan SSL 256-bit dan disimpan di server cloud bersertifikasi dengan pencadangan (backup) berkala. Pemilik bisnis memiliki kepemilikan penuh 100% atas datanya."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah kasir tetap bisa melayani pembeli saat koneksi internet toko mati?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya. Modul kasir POS COOCA dirancang dengan kapabilitas offline fallback. Kasir tetap dapat memasukkan pesanan, menghitung kembalian uang tunai, dan mencetak struk belanja. Ketika koneksi internet menyala kembali, transaksi akan tersinkronisasi otomatis ke cloud."
      }
    },
    {
      "@type": "Question",
      "name": "Bagaimana cara memindahkan daftar produk dan stok dari Excel lama?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Kami menyediakan template impor Excel resmi. Anda cukup mengisi kolom nama produk, harga jual, harga modal (HPP), dan stok awal, lalu mengunggahnya ke menu Produk COOCA dalam sekali klik."
      }
    }
  ]
}
</script>
@endpush

@section('content')
<div x-data="{ 
    activeCategory: 'all',
    searchQuery: '',
    openFaq: null,
    toggleFaq(idx) {
        this.openFaq = this.openFaq === idx ? null : idx;
        this.refreshIcons();
    },
    matches(category, title, content) {
        let matchesCat = this.activeCategory === 'all' || this.activeCategory === category;
        if (!this.searchQuery.trim()) return matchesCat;
        let q = this.searchQuery.toLowerCase();
        let matchesSearch = title.toLowerCase().includes(q) || content.toLowerCase().includes(q);
        return matchesCat && matchesSearch;
    },
    resetSearch() {
        this.searchQuery = '';
        this.activeCategory = 'all';
        this.refreshIcons();
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
    <!-- ═══ 1. HERO SECTION: 2-Grid Bento Apple HIG Canvas ══════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="pt-12 sm:pt-16 lg:pt-20 pb-12 sm:pb-16 border-b border-black/[0.06] dark:border-white/[0.08]">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <span>Pusat Sumber Daya</span>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Pusat Bantuan &amp; FAQ</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- KIRI: Headline, Value Proposition & Actions (Mobile Center, Desktop Left ~ 5 Cols) -->
                <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Kicker -->
                        <div class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            PUSAT BANTUAN &amp; TANYA JAWAB
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            Semua Jawaban Jelas untuk Menjalankan Usaha Tanpa Rasa Ragu
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        Penjelasan jujur dan transparan mengenai skema gratis, cara menghubungkan printer thermal Bluetooth, keamanan data bisnis, hingga cara kerja kasir saat internet toko sedang mati.
                    </p>

                    <!-- Quick Support Commitments (40-65 y.o. reassurance) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="check" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Jawaban Transparan</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Tanpa syarat tersembunyi</div>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                <i data-lucide="headphones" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white leading-tight">Dukungan WhatsApp</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5">Bantuan tim teknis manusia</div>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Action Buttons (Centered on Mobile, Row on Desktop) -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 w-full sm:w-auto">
                        <a href="#katalog-pertanyaan"
                            class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-all shadow-sm min-h-[48px]">
                            <span>Telusuri Pertanyaan Populer</span>
                            <i data-lucide="arrow-down" class="w-4 h-4 shrink-0"></i>
                        </a>
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20Tim%20COOCA,%20saya%20ingin%20bertanya%20seputar%20sistem%20kasir"
                            target="_blank" rel="noopener"
                            class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] hover:bg-slate-50 dark:hover:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] text-slate-800 dark:text-slate-200 text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98] min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                            <span>Tanya Langsung via WhatsApp</span>
                        </a>
                    </div>
                </div>

                <!-- KANAN: Real System & Compatibility Status Bento (7 Cols ~ 58%) -->
                <div class="lg:col-span-7">
                    <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">
                        
                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Status Kompatibilitas Sistem</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Standar Perangkat Keras COOCA</div>
                            </div>
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-[8px]">
                                Teruji di Lapangan
                            </span>
                        </div>

                        <!-- 4 Key Readiness Badges -->
                        <div class="space-y-2.5">
                            <div class="p-3 rounded-[14px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold text-xs shrink-0">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Printer Thermal Mini</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Bluetooth 58mm &amp; 80mm Auto-Cutter</div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">Plug &amp; Play</span>
                            </div>

                            <div class="p-3 rounded-[14px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i data-lucide="wifi-off" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Mode Kasir Offline</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Tetap transaksi &amp; cetak saat internet mati</div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">Otomatis Sync</span>
                            </div>

                            <div class="p-3 rounded-[14px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Keamanan Data Toko</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Isolasi tenant &amp; enkripsi bank-grade</div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">100% Aman</span>
                            </div>

                            <div class="p-3 rounded-[14px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[10px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Migrasi Data Produk</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Salin tempel template Excel 1 klik</div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">Format Baku</span>
                            </div>
                        </div>

                        <!-- Footer Helper -->
                        <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] text-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Tidak perlu beli perangkat kasir baru yang mahal</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. PENCARIAN & KATEGORI FILTER ═══════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section id="katalog-pertanyaan" class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-10">
        
        <div class="max-w-3xl mx-auto space-y-6 text-center">
            <div class="space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    PENCARIAN CEPAT
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Cari Jawaban Seputar Operasional Usaha Anda
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    Ketik kata kunci pertanyaan Anda atau pilih kategori topik di bawah ini.
                </p>
            </div>

            <!-- Instant Search Input (Apple HIG Styled, 48px Height, iOS Auto-Zoom Proof) -->
            <div class="relative max-w-xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input="refreshIcons()"
                    placeholder="Contoh: printer bluetooth, offline, gratis, excel..." 
                    class="w-full pl-11 pr-10 py-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#007AFF] focus:border-transparent text-[16px] sm:text-[14px] transition-all shadow-sm"
                />
                <button x-show="searchQuery.trim().length > 0" @click="searchQuery = ''; refreshIcons()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Category Pills Bar -->
            <div class="flex flex-wrap items-center justify-center gap-1.5 pt-2">
                <button 
                    @click="activeCategory = 'all'; refreshIcons();" 
                    :class="activeCategory === 'all' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Semua Pertanyaan
                </button>
                <button 
                    @click="activeCategory = 'pricing'; refreshIcons();" 
                    :class="activeCategory === 'pricing' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Biaya &amp; Lisensi
                </button>
                <button 
                    @click="activeCategory = 'hardware'; refreshIcons();" 
                    :class="activeCategory === 'hardware' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Hardware &amp; Printer
                </button>
                <button 
                    @click="activeCategory = 'security'; refreshIcons();" 
                    :class="activeCategory === 'security' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Keamanan Data
                </button>
                <button 
                    @click="activeCategory = 'pos'; refreshIcons();" 
                    :class="activeCategory === 'pos' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Kasir &amp; Mode Offline
                </button>
                <button 
                    @click="activeCategory = 'inventory'; refreshIcons();" 
                    :class="activeCategory === 'inventory' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Stok &amp; Resep BOM
                </button>
                <button 
                    @click="activeCategory = 'migration'; refreshIcons();" 
                    :class="activeCategory === 'migration' ? 'bg-[#007AFF] text-white font-bold' : 'bg-white dark:bg-[#1C1C1E] text-slate-600 dark:text-slate-400 border border-black/[0.06] dark:border-white/[0.08] hover:text-slate-900 dark:hover:text-white font-medium'"
                    class="px-3.5 py-2 rounded-[10px] text-xs transition-all shadow-sm">
                    Migrasi Excel
                </button>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ FAQ ACCORDION ITEMS ══════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════ -->
        <div class="max-w-3xl mx-auto space-y-3 pt-4">
            
            <!-- Item 1: Pricing -->
            <div x-show="matches('pricing', 'Apakah modul operasional esensial COOCA benar-benar gratis?', 'COOCA menyediakan modul esensial tanpa biaya bulanan tersembunyi yang mencakup modul Kasir (POS), Manajemen Produk dasar, dan Laporan Rekap Penjualan Harian.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(1)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF] shrink-0"></span>
                        Apakah modul operasional esensial COOCA benar-benar gratis?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    Ya, sepenuhnya benar. Visi kami adalah mendigitalkan operasional UMKM Indonesia tanpa hambatan modal awal. Anda dapat menggunakan modul Kasir POS, input katalog produk, pencatatan harga beli (HPP), dan rekonsiliasi kasir harian selamanya tanpa biaya tersembunyi atau masa kedaluwarsa uji coba.
                </div>
            </div>

            <!-- Item 2: Pricing upgrade -->
            <div x-show="matches('pricing', 'Kapan saya perlu beralih ke paket langganan berbayar?', 'Upgrade hanya diperlukan saat bisnis Anda membutuhkan fitur lanjutan seperti integrasi multi-cabang terpusat')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(2)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF] shrink-0"></span>
                        Kapan bisnis saya perlu beralih ke paket langganan berbayar?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 2" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    Anda hanya perlu upgrade jika skala bisnis Anda berkembang membutuhkan otomasi lebih tinggi, seperti:
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Pengelolaan lebih dari satu gerai fisik secara terpusat dari satu akun owner.</li>
                        <li>Integrasi WhatsApp Gateway untuk kirim nota digital &amp; penagihan kasbon otomatis.</li>
                        <li>Resep produksi multi-level (Bill of Materials) untuk F&amp;B atau manufaktur konveksi.</li>
                        <li>Pengaturan hak akses bertingkat untuk kasir, supervisor, dan staf gudang.</li>
                    </ul>
                </div>
            </div>

            <!-- Item 3: Hardware Devices -->
            <div x-show="matches('hardware', 'Perangkat apa saja yang didukung oleh COOCA?', 'COOCA berjalan berbasis web modern di smartphone Android iOS, tablet kasir, iPad, laptop, dan komputer PC.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(3)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        Perangkat apa saja yang didukung untuk kasir dan dashboard?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 3" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    COOCA dibangun menggunakan teknologi web app modern. Anda tidak perlu membeli mesin kasir POS khusus yang mahal seharga belasan juta rupiah. Anda dapat menggunakan:
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Tablet Android (Samsung, Xiaomi, Advan, dll) atau iPad untuk kasir meja.</li>
                        <li>Smartphone Android atau iPhone milik kasir untuk jualan keliling atau event bazar.</li>
                        <li>Laptop atau PC Windows/Macintosh apa pun menggunakan peramban Google Chrome atau Safari.</li>
                    </ul>
                </div>
            </div>

            <!-- Item 4: Hardware Printers -->
            <div x-show="matches('hardware', 'Printer thermal apa saja yang didukung?', 'COOCA mendukung printer thermal struk Bluetooth 58mm 80mm LAN Ethernet dan USB.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(4)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        Bagaimana kompatibilitas dengan printer thermal dan laci uang fisik?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 4" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    COOCA mendukung protokol standar industri ESC/POS yang digunakan oleh 99% printer thermal di pasaran:
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li><strong>Printer Bluetooth Mini (58mm):</strong> Merk Panda, VSC, Iware, Eppos, RPP02N, dll.</li>
                        <li><strong>Printer Struk Meja (80mm Auto-Cutter):</strong> Merk Epson TM-T82, Xprinter, Matrix Point.</li>
                        <li><strong>Printer Dapur LAN/KOT:</strong> Untuk cetak pesanan langsung ke koki dapur tanpa kabel USB.</li>
                        <li><strong>Cash Drawer (RJ11):</strong> Terhubung ke printer kasir dan otomatis terbuka saat transaksi tunai selesai dicetak.</li>
                    </ul>
                </div>
            </div>

            <!-- Item 5: Security -->
            <div x-show="matches('security', 'Bagaimana keamanan data bisnis saya?', 'Data Anda terisolasi dengan proteksi tenant ketat, dienkripsi saat transit dan di server cloud bersertifikasi.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(5)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        Apakah data keuangan dan resep bisnis saya aman dan terlindungi?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 5" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    Keamanan data adalah prioritas tertinggi arsitektur COOCA. Kami menerapkan isolasi multi-tenant ketat pada tingkat database, sehingga bisnis lain tidak akan pernah bisa mengakses data stok, supplier, maupun resep Anda. Seluruh transmisi dienkripsi dengan standar bank dan pencadangan database dilakukan secara otomatis harian.
                </div>
            </div>

            <!-- Item 6: POS Offline Mode -->
            <div x-show="matches('pos', 'Apakah bisa jualan saat internet mati?', 'Ya, modul kasir POS dirancang memiliki kapabilitas offline ringan untuk tetap mencetak struk belanjaan.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(6)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                        Bagaimana jika koneksi internet di toko fisik tiba-tiba mati saat jam sibuk?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 6 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 6" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    Antrean toko Anda tidak akan terganggu. Modul POS kasir COOCA dilengkapi mode offline lokal di peramban. Kasir tetap dapat mencari produk, memindai barcode, memasukkan pembayaran tunai, dan mencetak struk belanja. Begitu Wi-Fi atau kuota internet tersambung kembali, seluruh transaksi otomatis tersinkronisasi ke server.
                </div>
            </div>

            <!-- Item 7: Inventory & Recipe BOM -->
            <div x-show="matches('inventory', 'Bagaimana COOCA memotong stok untuk bisnis makanan & minuman (F&B)?', 'Melalui fitur Bill of Materials BOM Resep Otomatis. Stok bahan baku terpotong per gram saat menu terjual.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(7)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 shrink-0"></span>
                        Bagaimana sistem memotong stok bahan baku pada usaha kuliner (F&amp;B)?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 7 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 7" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    COOCA menggunakan fitur Bill of Materials (BOM) Resep Otomatis. Anda cukup memasukkan formula racikan sekali di awal (contoh: 1 porsi Kopi Susu = 18 gr Espresso + 150 ml Susu Segar + 1 Cup). Setiap kali kasir menyelesaikan transaksi menu tersebut, stok bahan baku di gudang otomatis terpotong. Anda bisa mencoba simulasinya di <a href="{{ route('kalkulator.hpp') }}" class="text-[#007AFF] dark:text-[#0A84FF] underline font-semibold">Kalkulator HPP COOCA</a>.
                </div>
            </div>

            <!-- Item 8: Migration & Excel -->
            <div x-show="matches('migration', 'Bisakah memindahkan data dari Excel lama?', 'Bisa! Kami menyediakan template impor Excel untuk produk, stok, dan data pelanggan hanya dalam sekali klik.')"
                 class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                <button @click="toggleFaq(8)" class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                    <span class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shrink-0"></span>
                        Bisakah saya memindahkan ribuan data produk dari catatan Excel lama?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="openFaq === 8 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 8" x-collapse class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                    Tentu saja bisa. Anda tidak perlu mengetik ulang produk satu per satu. Di COOCA telah disediakan fitur Impor Excel massal. Anda cukup mengunduh template spreadsheet resmi, menempelkan daftar SKU barang, harga modal, harga jual, dan stok gudang Anda, lalu mengunggahnya. Kunjungi halaman <a href="{{ route('template.index') }}" class="text-[#007AFF] dark:text-[#0A84FF] underline font-semibold">Template Operasional</a> untuk mengunduh berkasnya.
                </div>
            </div>

            <!-- Empty Search Results State -->
            <div x-show="!matches('all', '', '')" class="p-8 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                <i data-lucide="help-circle" class="w-8 h-8 text-slate-400 mx-auto"></i>
                <div class="font-bold text-base text-slate-900 dark:text-white">Pertanyaan Tidak Ditemukan</div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Tidak ada pertanyaan yang sesuai dengan kata kunci pencarian Anda. Silakan reset filter atau tanyakan langsung pada tim kami.
                </p>
                <button @click="resetSearch()" class="h-10 px-5 rounded-[12px] bg-[#007AFF] text-white text-xs font-semibold">
                    Tampilkan Semua Pertanyaan
                </button>
            </div>

        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 3. PUSAT ESKALASI DUKUNGAN (3 Asymmetric Bento Cards) ════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="border-t border-black/[0.06] dark:border-white/[0.08] py-16 sm:py-20 bg-white/50 dark:bg-[#151B2B]/40">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="max-w-2xl mx-auto text-center space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    PUSAT BANTUAN KHUSUS
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Pertanyaan Anda Belum Terjawab di Sini?
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    Tim spesialis dukungan COOCA siap membantu menjawab kendala teknis dan mendampingi digitalisasi gerai Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                
                <!-- Card 1: WhatsApp Hotline -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Konsultasi WhatsApp</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Hubungi langsung staf teknis kami untuk tanya jawab seputar printer kasir atau panduan awal setup toko.
                        </p>
                    </div>
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text={{ urlencode('Halo Tim COOCA, saya ingin tanya seputar sistem kasir dan aplikasi') }}" target="_blank" rel="noopener" class="h-11 px-4 rounded-[12px] bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Hubungi via WhatsApp</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Card 2: Panduan Operasional -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Panduan &amp; SOP Kasir</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Pelajari petunjuk teknis langkah demi langkah mulai dari pairing printer Bluetooth hingga penutupan shift kasir.
                        </p>
                    </div>
                    <a href="{{ route('public.resources.guides') }}" class="h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Buka Panduan SOP</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Card 3: Demo Interaktif -->
                <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i data-lucide="play" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Demonstrasi Kasir</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Uji coba antarmuka kasir POS langsung di peramban Anda tanpa perlu mendaftar akun terlebih dahulu.
                        </p>
                    </div>
                    <a href="{{ route('public.demo') }}" class="h-11 px-4 rounded-[12px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <span>Coba Demo Kasir</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 4. CONVERSION CTA SECTION ════════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
        <div class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-center space-y-6 shadow-sm">
            <div class="max-w-2xl mx-auto space-y-3">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    DIGITALISASI MUDAH
                </div>
                <h3 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Mulai Digitalisasi Usaha Anda Hari Ini
                </h3>
                <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                    Daftar sekarang dan nikmati modul kasir esensial gratis tanpa batasan waktu. Kelola transaksi, stok barang, dan rekap keuangan dalam satu sentuhan.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-2">
                <a href="{{ route('register') }}"
                    class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm inline-flex items-center gap-2 active:scale-[0.98] transition-all shadow-sm">
                    <span>Buat Akun Bisnis Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('public.resources.case-studies') }}"
                    class="h-12 px-7 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] hover:bg-slate-200/70 dark:hover:bg-[#38383A] text-slate-900 dark:text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                    <i data-lucide="award" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                    <span>Baca Kisah Sukses UMKM</span>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
