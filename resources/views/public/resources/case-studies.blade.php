@extends('layouts.public_marketing')

@section('title', 'Studi Kasus & Kisah Sukses UMKM Indonesia: Transformasi Bisnis Nyata | COOCA')
@section('description', 'Pelajari bagaimana pelaku UMKM F&B, retail, bengkel, laundry, fashion, dan konveksi berhasil mengeliminasi selisih stok, memangkas biaya operasional, dan melipatgandakan profit bersama COOCA.')
@section('og_title', 'Studi Kasus & Kisah Sukses UMKM Indonesia: Transformasi Bisnis Nyata | COOCA')
@section('og_description', 'Pelajari kisah nyata pemilik bisnis Indonesia mengeliminasi kebocoran stok, memangkas waktu rekap kasir, dan menumbuhkan cabang bersama COOCA.')
@section('canonical', route('public.resources.case-studies'))
@section('og_type', 'article')
@section('keywords', 'studi kasus umkm, kisah sukses bisnis, efisiensi bisnis umkm, sistem kasir multi cabang, kontrol hpp makanan, aplikasi bengkel motor, software erp indonesia')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
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
    <div x-data="{
        activeFilter: 'all',
        openFaq: null,
        activeCaseModal: null,
        toggleFaq(idx) {
            this.openFaq = this.openFaq === idx ? null : idx;
            this.refreshIcons();
        },
        openDetail(caseId) {
            this.activeCaseModal = caseId;
            this.refreshIcons();
        },
        closeDetail() {
            this.activeCaseModal = null;
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
                <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-6"
                    aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}"
                        class="hover:text-slate-900 dark:hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                    <span>Pusat Sumber Daya</span>
                    <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                    <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Studi Kasus
                        UMKM</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    <!-- KIRI: Headline, Value Proposition & Actions (5 Cols ~ 42%) -->
                    <div class="lg:col-span-5 space-y-6">
                        <!-- Pure Typographic Kicker -->
                        <div
                            class="text-[12px] sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            STUDI KASUS &amp; BUKTI NYATA
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.2] text-balance">
                            Kisah Nyata UMKM yang Berhasil Menutup Kebocoran Kasir dan Naik Kelas
                        </h1>

                        <p
                            class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty max-w-xl">
                            Bukan sekadar teori manajemen. Pelajari bagaimana pemilik kedai kopi, minimarket kelontong,
                            bengkel motor, dan konveksi menata ulang operasional harian, menghentikan selisih uang kasir,
                            dan membuka cabang baru dengan tenang.
                        </p>

                        <!-- Tangible Impact Indicators (40-65 y.o. focus) -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1 text-left">
                            <div
                                class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">12.4%
                                </div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Hemat Biaya
                                    Bahan</div>
                            </div>

                            <div
                                class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                <div class="text-2xl font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums">5 Menit
                                </div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Tutup Buku
                                    Tiap Shift</div>
                            </div>

                            <div
                                class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">99.8%</div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Kesesuaian
                                    Stok</div>
                            </div>

                            <div
                                class="p-3.5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 tabular-nums">0%</div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">Nota Kasbon
                                    Hilang</div>
                            </div>
                        </div>

                        <!-- Direct Action Buttons -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="#katalog-studi-kasus"
                                class="h-12 px-7 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-all shadow-sm">
                                <span>Pilih Kategori Usaha Anda</span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('register') }}"
                                class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] hover:bg-slate-50 dark:hover:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] text-slate-800 dark:text-slate-200 text-sm font-semibold flex items-center justify-center gap-2 transition active:scale-[0.98]">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                <span>Coba COOCA Gratis</span>
                            </a>
                        </div>
                    </div>

                    <!-- KANAN: Real Verification UI Bento (F&B Multi-Outlet Audit Card - 7 Cols ~ 58%) -->
                    <div class="lg:col-span-7">
                        <div
                            class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-4">

                            <!-- Header Verification -->
                            <div
                                class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Audit Operasional
                                        Terverifikasi</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Kopi Sudut Santai - 3 Gerai
                                        (Bandung)</div>
                                </div>
                                <span
                                    class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-[8px]">
                                    Sukses Terverifikasi
                                </span>
                            </div>

                            <!-- Before & After Direct Comparison -->
                            <div class="space-y-2.5">
                                <div
                                    class="p-3.5 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                    <div
                                        class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                        Kondisi Sebelum Sistem</div>
                                    <div class="text-xs text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                        Takaran susu berbeda per barista. Selisih 45 liter susu fresh per bulan tanpa
                                        kejelasan transaksi kasir.
                                    </div>
                                </div>

                                <div
                                    class="p-3.5 rounded-[14px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12] border border-emerald-500/20">
                                    <div
                                        class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                        Hasil Setelah COOCA</div>
                                    <div
                                        class="text-xs text-slate-800 dark:text-slate-200 mt-1 leading-relaxed font-medium">
                                        Resep otomatis memotong stok bahan per porsi kopi. Biaya bahan baku (Food Cost)
                                        terpangkas 12.4% dan kasir tutup buku dalam 5 menit.
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline & Modules -->
                            <div
                                class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    <span>Waktu Migrasi: 2 Hari Kerja</span>
                                </div>
                                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">Modul: Kasir + BOM Resep</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 2. KATALOG STUDI KASUS & FILTER TAB ═════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section id="katalog-studi-kasus" class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-10">

            <div
                class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="max-w-2xl space-y-2">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        DAFTAR KASUS PER INDUSTRI
                    </div>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Pilih Sektor Bisnis yang Relevan dengan Usaha Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                        Setiap industri memiliki tantangan operasional yang berbeda. Telusuri solusi yang telah teruji di
                        lapangan.
                    </p>
                </div>

                <!-- Segmented Control Filters -->
                <div
                    class="flex flex-wrap items-center gap-1.5 p-1.5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-[16px] shadow-sm">
                    <button @click="activeFilter = 'all'; refreshIcons();"
                        :class="activeFilter === 'all' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Semua Industri
                    </button>
                    <button @click="activeFilter = 'fnb'; refreshIcons();"
                        :class="activeFilter === 'fnb' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        F&amp;B / Resto
                    </button>
                    <button @click="activeFilter = 'retail'; refreshIcons();"
                        :class="activeFilter === 'retail' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Retail &amp; Toko
                    </button>
                    <button @click="activeFilter = 'workshop'; refreshIcons();"
                        :class="activeFilter === 'workshop' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Bengkel &amp; Servis
                    </button>
                    <button @click="activeFilter = 'services'; refreshIcons();"
                        :class="activeFilter === 'services' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Laundry &amp; Jasa
                    </button>
                    <button @click="activeFilter = 'fashion'; refreshIcons();"
                        :class="activeFilter === 'fashion' ? 'bg-[#007AFF] text-white font-bold' :
                            'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3.5 py-2 rounded-[10px] text-xs transition-all">
                        Konveksi &amp; Fashion
                    </button>
                </div>
            </div>

            <!-- Bento Grid of Case Studies -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Case 1: Kopi Sudut Santai -->
                <div x-show="activeFilter === 'all' || activeFilter === 'fnb'"
                    class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-[8px]">
                                F&amp;B / Kedai Kopi (3 Gerai)
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Hemat 12.4% Bahan
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                            Kopi Sudut Santai: Resep Otomatis Memotong Bahan Basi &amp; Selisih Susu
                        </h3>

                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                Takaran bahan beda per barista. 45 liter susu hilang tiap bulan tanpa nota jelas.
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                Resep BOM terpotong otomatis tiap pesanan kasir dan terhubung printer dapur KOT.
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('public.solutions.fnb') }}"
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Solusi F&amp;B &rarr;
                        </a>
                        <button @click="openDetail(1)"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <span>Baca Kisah Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Case 2: Toko Kelontong Berkah -->
                <div x-show="activeFilter === 'all' || activeFilter === 'retail'"
                    class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-500/10 px-2.5 py-1 rounded-[8px]">
                                Retail / Kelontong &amp; Grosir
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Piutang Macet Turun 85%
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                            Toko Kelontong Berkah: Bon Hutang Nol Macet dengan Pengingat Otomatis
                        </h3>

                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                Bon kasbon ditulis di buku tulis kertas yang sering robek dan lupa ditagih berbulan-bulan.
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                Batas pagu hutang otomatis per pelanggan &amp; rekap pengingat via WhatsApp terjadwal.
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('public.solutions.retail') }}"
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Solusi Retail &rarr;
                        </a>
                        <button @click="openDetail(2)"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <span>Baca Kisah Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Case 3: Bengkel Motor Perkasa -->
                <div x-show="activeFilter === 'all' || activeFilter === 'workshop'"
                    class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs font-bold text-purple-600 dark:text-purple-400 bg-purple-500/10 px-2.5 py-1 rounded-[8px]">
                                Otomotif / Bengkel &amp; Sparepart
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Servis Naik 35%
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                            Bengkel Motor Perkasa: Tertib SPK Digital &amp; Transparansi Komisi Montir
                        </h3>

                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                Oli dan sparepart keluar laci tanpa nota. Komisi bagi hasil 6 montir sering memicu ribut.
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                Surat Perintah Kerja (SPK) digital terikat nomor polisi motor &amp; komisi jasa terhitung
                                otomatis.
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('public.solutions.workshop') }}"
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Solusi Bengkel &rarr;
                        </a>
                        <button @click="openDetail(3)"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <span>Baca Kisah Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Case 4: Fresh Laundry Express -->
                <div x-show="activeFilter === 'all' || activeFilter === 'services'"
                    class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs font-bold text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 px-2.5 py-1 rounded-[8px]">
                                Jasa / Laundry Kiloan &amp; Satuan
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                0% Baju Tertukar
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                            Fresh Laundry Express: Zero Baju Tertukar dengan Pelacakan QR Rak
                        </h3>

                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                Nota bon kertas luntur basah kena detergen. Pakaian pelanggan sering tertukar di rak saat
                                ramai.
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                Label barcode per kantong, nomor rak digital, dan WhatsApp otomatis saat cuci selesai.
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('public.solutions.services') }}"
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Solusi Jasa &rarr;
                        </a>
                        <button @click="openDetail(4)"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <span>Baca Kisah Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Case 5: Boutique Hijab Syari -->
                <div x-show="activeFilter === 'all' || activeFilter === 'fashion'"
                    class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-500/10 px-2.5 py-1 rounded-[8px]">
                                Fashion / Butik &amp; Pakaian
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Stok Multi-Kanal Sinkron
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                            Boutique Hijab Syari: Stok Live TikTok &amp; Toko Fisik Terkunci Akurat
                        </h3>

                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                Barang live streaming malam terjual 50 pcs, tapi siangnya masih dibeli orang di toko fisik.
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                Sentralisasi SKU variasi warna/ukuran dengan sinkronisasi inventori real-time.
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('public.solutions.retail') }}"
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Solusi Inventori &rarr;
                        </a>
                        <button @click="openDetail(5)"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <span>Baca Kisah Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Case 6: Konveksi Maju Bersama -->
                <div x-show="activeFilter === 'all' || activeFilter === 'fashion'"
                    class="flex flex-col bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-sm hover:border-[#007AFF]/40 transition-all justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-[8px]">
                                Manufaktur / Konveksi &amp; Sablon
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Margin Terkunci 22%
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                            Konveksi Maju Bersama: Presisi HPP Produksi Seragam Sebelum Tender
                        </h3>

                        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2">
                            <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E]">
                                <strong class="text-rose-600 dark:text-rose-400 block mb-0.5">Tantangan:</strong>
                                Pasang harga tender modal kira-kira. Saat bahan katun naik, proyek berakhir impas nombok.
                            </div>
                            <div class="p-3 rounded-[12px] bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12]">
                                <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Solusi COOCA:</strong>
                                Estimator HPP multi-bahan (kain, benang, sablon) &amp; tracking uang muka (DP) bertahap.
                            </div>
                        </div>
                    </div>

                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('kalkulator.hpp') }}"
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Kalkulator HPP &rarr;
                        </a>
                        <button @click="openDetail(6)"
                            class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066DF] text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <span>Baca Kisah Lengkap</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 3. MATRIKS PERBANDINGAN SEBELUM VS SESUDAH ═══════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="border-y border-black/[0.06] dark:border-white/[0.08] py-16 sm:py-20 bg-white/50 dark:bg-[#151B2B]/40">
            <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="max-w-2xl space-y-2">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        MATRIKS TRANSFORMASI
                    </div>
                    <h2
                        class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Operasional Sebelum vs Sesudah Menggunakan COOCA
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                        Perubahan langsung yang dirasakan oleh pemilik usaha dan staf kasir setelah menggunakan sistem yang
                        rapi.
                    </p>
                </div>

                <!-- Bento Comparison Table -->
                <div
                    class="overflow-x-auto rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-black/[0.06] dark:border-white/[0.08] text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="py-4 px-5 w-1/4">Aspek Operasional</th>
                                <th class="py-4 px-5 w-3/8 text-rose-600 dark:text-rose-400 bg-rose-500/[0.03]">Sebelum
                                    Pakai COOCA</th>
                                <th class="py-4 px-5 w-3/8 text-emerald-600 dark:text-emerald-400 bg-emerald-500/[0.03]">
                                    Sesudah Pakai COOCA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.06] dark:divide-white/[0.08] text-sm">
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Tutup Kasir Harian</td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Manual hitung
                                    nota kertas 2–3 jam tiap malam, rawan selisih uang kas fisik.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Otomatis balance dalam 5 menit, laporan kasir langsung terkirim ke WhatsApp owner.</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Kontrol Stok &amp; Bahan
                                    Baku</td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Stok hanya
                                    dihitung saat opname bulanan, selisih bahan basi sering tidak terlacak.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Resep BOM terpotong otomatis tiap transaksi kasir, alert stok menipis real-time.</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Piutang &amp; Kasbon
                                    Pelanggan</td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Dicatat di bon
                                    kertas terselip, sering lupa ditagih sampai berbulan-bulan.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Plafon kredit terkunci di sistem, pengingat jatuh tempo otomatis via WhatsApp.</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">Pengawasan Multi-Cabang
                                </td>
                                <td class="py-4 px-5 text-slate-600 dark:text-slate-400 bg-rose-500/[0.03]">Owner harus
                                    datang keliling fisik ke tiap cabang untuk cek setoran tunai.</td>
                                <td class="py-4 px-5 text-slate-900 dark:text-slate-100 font-medium bg-emerald-500/[0.03]">
                                    Dashboard sentral dari HP, omzet dan laba per cabang terlihat detik demi detik.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 4. FAQ IMPLEMENTASI STUDI KASUS ══════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 space-y-6">
            <div class="text-center space-y-2">
                <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                    TANYA JAWAB IMPLEMENTASI
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Pertanyaan Seputar Penerapan COOCA di Lapangan
                </h2>
            </div>

            <div class="space-y-3 pt-2">
                <div
                    class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(1)"
                        class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Berapa lama proses implementasi dari sistem manual ke COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                            :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 1" x-collapse
                        class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Rata-rata UMKM hanya membutuhkan 1 hingga 3 hari kerja. Anda dapat langsung mengimpor data produk
                        dan pelanggan menggunakan template Excel yang sudah kami sediakan, lalu kasir dapat langsung
                        digunakan pada tablet atau komputer apa pun tanpa instalasi server lokal rumit.
                    </div>
                </div>

                <div
                    class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(2)"
                        class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Apakah staf dan kasir yang belum terbiasa komputer bisa mengoperasikannya?</span>
                        <i data-lucide="chevron-down"
                            class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                            :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse
                        class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Sangat bisa. Antarmuka kasir COOCA dirancang dengan tombol sentuh besar (min 48px), visual foto
                        menu/produk yang jelas, dan alur pembayaran satu sentuhan. Staf baru rata-rata dapat melayani
                        pembeli secara mandiri dalam waktu latihan kurang dari 30 menit.
                    </div>
                </div>

                <div
                    class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm transition-all">
                    <button @click="toggleFaq(3)"
                        class="w-full flex items-center justify-between gap-4 text-left font-bold text-base text-slate-900 dark:text-white">
                        <span>Bagaimana jika koneksi internet di toko fisik tiba-tiba mati?</span>
                        <i data-lucide="chevron-down"
                            class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                            :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse
                        class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-black/[0.06] dark:border-white/[0.08] pt-3">
                        Modul Kasir POS COOCA mendukung mode offline lokal. Kasir tetap dapat melayani antrean belanja,
                        menghitung kembalian uang tunai, dan mencetak struk thermal. Ketika koneksi internet menyala
                        kembali, transaksi akan otomatis tersinkronisasi ke server pusat.
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 5. CONVERSION CTA SECTION ════════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-20">
            <div
                class="p-8 sm:p-12 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-center space-y-6 shadow-sm">
                <div class="max-w-2xl mx-auto space-y-3">
                    <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        MULAI TRANSFORMASI USAHA
                    </div>
                    <h3
                        class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Siap Menjadikan Usaha Anda Kisah Sukses Berikutnya?
                    </h3>
                    <p class="text-base text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                        Tinggalkan pencatatan manual yang menguras tenaga dan waktu istirahat Anda. Uji coba COOCA sekarang
                        dan rasakan kemudahan mengontrol bisnis dari genggaman.
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
        <!-- ═══ 6. MODAL SHEET: Detail Kisah Studi Kasus ═════════════════════════════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <div x-show="activeCaseModal !== null" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-case-title" role="dialog" aria-modal="true">

            <div class="fixed inset-0 bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-opacity"
                @click="closeDetail()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-8 space-y-6">

                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div>
                            <div class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                                DETAIL STUDI KASUS</div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-case-title">
                                Langkah Transformasi Operasional
                            </h3>
                        </div>
                        <button @click="closeDetail()"
                            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#2C2C2E] text-slate-500 hover:text-slate-900 dark:hover:text-white flex items-center justify-center">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Case 1 Details -->
                    <div x-show="activeCaseModal === 1" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-base text-slate-900 dark:text-white">Kopi Sudut Santai - 3 Gerai
                            (Bandung)</div>
                        <p class="text-sm leading-relaxed">Sebelumnya pemilik kedai sering mengeluhkan selisih stok susu
                            cair dan bubuk kopi espresso yang mencapai Rp 2,8 juta per bulan tanpa bisa melacak shift mana
                            yang boros.</p>

                        <div class="space-y-2 pt-1">
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 1: Standardisasi
                                    Takaran (BOM)</strong>
                                <span class="text-xs">Setiap menu kopi dikunci resep gramasi resminya ke dalam sistem COOCA
                                    (contoh: 18 gr espresso, 150 ml susu).</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 2: Integrasi
                                    Printer Dapur Barista</strong>
                                <span class="text-xs">Pesanan kasir langsung tercetak otomatis ke meja barista,
                                    menghentikan kebiasaan memberi porsi gratis tanpa tiket.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Hasil: Penghematan Rp 3,2
                                    Juta / Bulan</strong>
                                <span class="text-xs">Food cost turun 12.4% dan pemilik kini dapat memantau stok susu
                                    ketiga cabang cukup dari handphone.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 2 Details -->
                    <div x-show="activeCaseModal === 2" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-base text-slate-900 dark:text-white">Toko Kelontong Berkah (Cirebon)
                        </div>
                        <p class="text-sm leading-relaxed">Pemilik toko mengelola lebih dari 2.500 SKU barang kebutuhan
                            pokok dan melayani kasbon pelanggan warga sekitar. Rekap piutang yang tercecer menyebabkan modal
                            macet.</p>

                        <div class="space-y-2 pt-1">
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 1: Impor Excel
                                    2.500 Barang</strong>
                                <span class="text-xs">Katalog barang dan modal harga beli dimasukkan ke COOCA dalam 10
                                    menit via impor spreadsheet.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 2: Plafon Kasbon
                                    Digital</strong>
                                <span class="text-xs">Setiap pelanggan diberi limit kredit maksimal. Transaksi kasir
                                    menolak otomatis jika hutang lama belum dilunasi.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Hasil: Arus Kas Kembali
                                    Sehat</strong>
                                <span class="text-xs">Piutang macet berkurang 85% dalam 60 hari berkat pesan pengingat
                                    jatuh tempo WhatsApp otomatis.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 3 Details -->
                    <div x-show="activeCaseModal === 3" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-base text-slate-900 dark:text-white">Bengkel Motor Perkasa (Semarang)
                        </div>
                        <p class="text-sm leading-relaxed">Sering terjadi komplain montir merasa komisi servisnya dipotong,
                            sementara pemilik bengkel sering mendapati stok kampas rem dan oli berkurang tanpa ada nota.</p>

                        <div class="space-y-2 pt-1">
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 1: SPK Digital
                                    Plat Nomor</strong>
                                <span class="text-xs">Setiap motor masuk wajib input plat nomor dan keluhan di tablet kasir
                                    penerima servis.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 2: Ambil
                                    Sparepart Berbasis SPK</strong>
                                <span class="text-xs">Bagian gudang hanya mengeluarkan sparepart bila sudah tercatat di SPK
                                    resmi sistem.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Hasil: Bebas Selisih
                                    &amp; Montir Puas</strong>
                                <span class="text-xs">Komisi jasa mekanik terhitung otomatis secara transparan. Antrean
                                    servis meningkat 35%.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 4 Details -->
                    <div x-show="activeCaseModal === 4" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-base text-slate-900 dark:text-white">Fresh Laundry Express (Surabaya)
                        </div>
                        <p class="text-sm leading-relaxed">Menangani hingga 400 kg cucian per hari. Masalah utama adalah
                            nota bon kertas basah terkena detergen dan pakaian pelanggan sering tertukar saat jam sibuk.</p>

                        <div class="space-y-2 pt-1">
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 1: Label Barcode
                                    Anti-Air</strong>
                                <span class="text-xs">Struk barcode ditempelkan pada keranjang cucian saat penimbangan
                                    awal.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah 2: Alur Rak
                                    Selesai Siap Ambil</strong>
                                <span class="text-xs">Saat disetrika dan masuk rak, kasir memindai barcode dan status
                                    WhatsApp otomatis terkirim ke pelanggan.</span>
                            </div>
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Hasil: Nol Komplain
                                    Pakaian Hilang</strong>
                                <span class="text-xs">Kecepatan pengambilan cucian meningkat dan kepuasan pelanggan naik
                                    signifikan.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 5 Details -->
                    <div x-show="activeCaseModal === 5" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-base text-slate-900 dark:text-white">Boutique Hijab Syari (Solo)</div>
                        <p class="text-sm leading-relaxed">Menjual pakaian muslimah via siaran live media sosial sekaligus
                            memiliki butik offline. Stok sering bentrok karena penjualan malam belum tercatat saat toko buka
                            pagi.</p>

                        <div class="space-y-2 pt-1">
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah: Satu Database
                                    Sentral</strong>
                                <span class="text-xs">Seluruh pesanan live diinput ke kasir COOCA sehingga etalase offline
                                    langsung mengetahui sisa stok riil.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 6 Details -->
                    <div x-show="activeCaseModal === 6" class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                        <div class="font-bold text-base text-slate-900 dark:text-white">Konveksi Maju Bersama (Pekalongan)
                        </div>
                        <p class="text-sm leading-relaxed">Menerima pesanan seragam instansi dan komunitas. Kerap kali
                            merugi karena kenaikan harga bahan kain katun di tengah proses penjahitan.</p>

                        <div class="space-y-2 pt-1">
                            <div
                                class="p-3 rounded-[12px] bg-[#F9F9FB] dark:bg-[#242426] border border-black/[0.04] dark:border-white/[0.06]">
                                <strong class="text-slate-900 dark:text-white block text-xs mb-1">Langkah: Simulasi Biaya
                                    HPP Bahan &amp; Upah</strong>
                                <span class="text-xs">Menghitung kebutuhan kain per meter, kancing, benang, sablon, dan
                                    upah jahit sebelum mengajukan penawaran harga.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div
                        class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                        <a href="{{ route('register') }}"
                            class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                            Mulai Terapkan di Usaha Anda &rarr;
                        </a>
                        <button @click="closeDetail()"
                            class="h-10 px-5 rounded-[12px] bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-semibold">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
