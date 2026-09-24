@extends('layouts.public_marketing')

@section('title', 'Software Pembuatan Konten Promosi & Workflow Kreasi Bisnis | COOCA')
@section('description',
    'Transformasikan produk toko fisik Anda menjadi materi promosi media sosial yang memikat. Workflow kreasi konten terpadu: copywriting multi-format, visual produk, dan persetujuan draf sebelum tayang.')
@section('og_title', 'Software Pembuatan Konten Promosi & Workflow Kreasi Bisnis | COOCA')
@section('og_description',
    'Transformasikan produk toko fisik Anda menjadi materi promosi media sosial yang memikat. Workflow kreasi konten terpadu: copywriting multi-format, visual produk, dan persetujuan draf sebelum tayang.')
@section('keywords',
    'software pembuatan konten promosi, workflow kreasi konten bisnis, generator materi promosi produk, copywriting katalog toko, studio konten umkm')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA Content Creation Studio",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Studio workflow pembuatan konten pemasaran dan materi promosi produk yang terintegrasi langsung dengan database inventori toko.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Kreasi materi promosi langsung dari database foto dan harga produk POS",
    "Adaptasi copywriting otomatis ke berbagai format: Instagram Feed, Reels, dan WhatsApp",
    "Template narasi pemasaran siap pakai untuk F&B, retail, dan bisnis jasa",
    "Alur persetujuan (approval) draf konten antara tim kreator dan pemilik bisnis",
    "Pustaka aset visual terpusat untuk menjaga konsistensi identitas brand"
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
      "name": "Bagaimana modul ini membantu saya membuat konten promosi produk toko?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Anda cukup memilih produk dari modul Inventori & POS. Sistem secara otomatis menarik nama barang, harga, keunggulan bahan, dan foto produk, lalu menyusun alternatif teks caption promosi yang siap diedit dan dijadwalkan."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah teks promosi yang dihasilkan bisa disesuaikan dengan gaya bicara brand kami?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mengatur Brand Voice (nada bicara brand)-misalnya kasual dan hangat untuk kafe kopi anak muda, atau profesional dan terpercaya untuk bengkel dan klinik kecantikan."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah staf atau admin konten bisa langsung menerbitkan postingan tanpa sepengetahuan saya?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak bisa, jika Anda mengaktifkan alur approval. Staf hanya dapat menyusun draf materi promosi ke status 'Menunggu Review'. Konten hanya akan masuk ke antrean publikasi setelah Anda atau manajer marketing menyetujuinya."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah materi promosi bisa langsung dibagikan ke pesan WhatsApp pelanggan?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Setiap materi konten yang dibuat menyediakan format teks pendek yang ringkas dan ramah, lengkap dengan link katalog belanja yang siap disebarkan ke daftar broadcast pelanggan."
      }
    }
  ]
}
</script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION (Adopted from landing.blade.php concept - Full Viewport) --}}
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Subtle Ambient Background Glows (Pure CSS) --}}
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-10 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-center w-full">
                    
                    {{-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof --}}
                    <div
                        class="lg:col-span-5 space-y-5 sm:space-y-6 text-left flex flex-col items-start w-full">
                        
                        <!-- Pure Typographic Overline Kicker with Pulse Dot -->
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Product-Driven Content Creation Studio
                            </p>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[2.65rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words">
                                Ubah Produk &amp; Menu Toko Menjadi <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Konten Promosi yang Menjual.</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-xl">
                            Berhenti membuang waktu berjam-jam memikirkan ide postingan media sosial dari nol. Pilih produk
                            langsung dari katalog toko Anda, susun variasi pesan untuk Instagram, TikTok, dan WhatsApp, lalu
                            tinjau draf bersama tim sebelum otomatis dipublikasikan.
                        </p>

                        {{-- Action Buttons --}}
                        <div class="pt-1 flex flex-row flex-wrap items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-5 sm:px-8 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-base flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Coba Studio Konten</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.content.calendar') }}"
                                class="h-10 sm:h-12 px-4 sm:px-6 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 border border-white/15 backdrop-blur-sm transition-all min-h-[40px] sm:min-h-[48px]">
                                <span>Lihat Kalender Konten</span>
                            </a>
                        </div>

                        {{-- Key Trust Checkpoints --}}
                        <div
                            class="pt-1 flex flex-wrap items-center justify-start gap-x-4 gap-y-1.5 text-[11px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Sumber dari Katalog Stok Riil</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Format Feed, Story &amp; Chat</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Alur Review Draf Tim</span>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: Simulated Live Content Creation Studio Bento Glass UI --}}
                    <div class="lg:col-span-7 relative w-full">
                        <!-- Ambient Spotlight Glow -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] blur-2xl pointer-events-none -z-10">
                        </div>

                        <div
                            class="relative rounded-2xl sm:rounded-3xl bg-[#0E1E45]/85 p-4 sm:p-6 shadow-2xl border border-white/15 ring-1 ring-white/10 backdrop-blur-xl text-white">

                            {{-- Studio Top Bar --}}
                            <div class="flex items-center justify-between gap-2 pb-3.5 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <span class="p-2 rounded-xl bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-sm truncate">Studio Pembuatan Konten</div>
                                        <div class="text-[11px] text-slate-400 truncate">Sumber: Katalog POS • SKU-112</div>
                                    </div>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-400 text-[11px] font-mono font-bold shrink-0">Draf Siap Review</span>
                            </div>

                            {{-- Product Source Selector Pill --}}
                            <div
                                class="p-3 my-3.5 rounded-2xl bg-[#060B1E]/90 border border-white/10 flex items-center justify-between gap-2.5 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-amber-400 shrink-0">
                                        <i data-lucide="coffee" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-white text-[12px] truncate">Croissant Butter Fresh Oven</div>
                                        <div class="text-[10.5px] text-slate-400 font-mono truncate">Rp 28.000 • Stok Sudirman: 15 pcs</div>
                                    </div>
                                </div>
                                <span class="text-[11px] text-[#00C4D8] font-medium shrink-0">Ganti Produk</span>
                            </div>

                            {{-- Multi-Channel Variant Generator Preview --}}
                            <div class="space-y-2.5 text-xs text-left">
                                <div class="text-[10.5px] uppercase font-mono tracking-wider text-slate-400 px-1">Variasi Materi Pemasaran:</div>

                                {{-- Variant 1: Instagram Feed --}}
                                <div class="p-3 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
                                    <div class="flex items-center justify-between text-[10.5px]">
                                        <span class="font-bold text-[#00C4D8] flex items-center gap-1.5">
                                            <i data-lucide="instagram" class="w-3.5 h-3.5"></i> Instagram Feed &amp; Caption
                                        </span>
                                        <span class="text-slate-400">Tone: Hangat</span>
                                    </div>
                                    <p class="text-[11.5px] text-slate-300 leading-relaxed font-sans">
                                        "Renyah di gigitan pertama, lembut dan wangi butter di dalam! Temani secangkir
                                        kopi pagimu di Sudirman &amp; Senopati. Dibuat fresh setiap pagi untuk harimu yang
                                        produktif..."
                                    </p>
                                </div>

                                {{-- Variant 2: WhatsApp Promo Broadcast --}}
                                <div class="p-3 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
                                    <div class="flex items-center justify-between text-[10.5px]">
                                        <span class="font-bold text-emerald-400 flex items-center gap-1.5">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Pesan Siaran WhatsApp
                                        </span>
                                        <span class="text-slate-400">Format Singkat</span>
                                    </div>
                                    <p class="text-[11.5px] text-slate-300 leading-relaxed font-sans">
                                        "Halo Kak! Promo kilat pagi ini: Beli 2 Croissant Butter gratis 1 Americano sebelum
                                        jam 11:00. Tunjukkan pesan ini ke kasir ya!"
                                    </p>
                                </div>
                            </div>

                            {{-- Review & Approval Footer Action --}}
                            <div
                                class="mt-3.5 pt-2.5 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <span class="text-slate-400 text-[10.5px] truncate">Disusun: Admin Sarah (10:15 WIB)</span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button
                                        class="px-2.5 py-1 rounded-lg bg-white/10 text-slate-300 text-[10.5px] hover:bg-white/20">Revisi</button>
                                    <button
                                        class="px-3.5 py-1.5 rounded-lg bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-[10.5px] transition-colors">
                                        Setujui &amp; Jadwalkan
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerumitan Membuat Konten Secara Manual --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-b border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                        Tantangan Pembuatan Konten
                    </h2>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Mengapa Tim Pemasaran Anda Sering Kehabisan Ide Konten Promosi?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-3">
                        Banyak bisnis memiliki produk unggulan yang hebat, namun kesulitan mengomunikasikan nilainya di
                        media sosial secara teratur karena alur pembuatan materi yang rumit.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Writer's Block Setiap Hari</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Menghabiskan 2 jam setiap pagi hanya untuk menatap layar kosong dan bingung harus menulis
                            caption apa untuk mempromosikan produk toko hari ini.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                            <i data-lucide="folder-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Foto & Data Produk Tercecer</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Foto produk tersimpan di Google Drive, harga ada di kasir POS, dan spesifikasi bahan ada di
                            catatan dapur. Proses mengumpulkan bahan memakan waktu seharian.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="message-square-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Salah Tulis Harga & Promo</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Admin konten salah mencantumkan harga diskon di Instagram tanpa verifikasi, menyebabkan komplain
                            dari pembeli saat membayar di meja kasir toko.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE STUDIO CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-3">
                    Fitur Studio Kreasi COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Alur Kerja Konten Profesional Tanpa Biaya Agensi
                </p>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">
                    Memadukan data fisik produk toko dengan kemudahan menyusun pesan promosi yang persuasif.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Product-Driven Drafting (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 flex items-center justify-center text-[#007AFF]">
                            <i data-lucide="box" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Kreasi Konten Langsung dari Produk Nyata
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Cukup pilih item dari sistem POS atau stok gudang Anda. Sistem langsung menyajikan foto produk
                            resmi, harga asli, keunggulan bahan baku, dan tautan belanja yang tervalidasi tanpa risiko salah
                            ketik harga.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">Validasi Harga:</span>
                        <span class="text-[#007AFF] font-semibold flex items-center gap-1 shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Harga promosi selalu sesuai sistem kasir
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Alur Persetujuan Tim (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                            <i data-lucide="check-square" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Alur Persetujuan & Koreksi Draf
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Staf konten menyiapkan draf teks dan materi visual, sementara pemilik bisnis atau manajer
                            marketing dapat menyetujui atau menambahkan catatan revisi sebelum jadwal tayang.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/80 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Kualitas Terjaga</span>
                        <span class="text-purple-500 font-bold shrink-0">100% Konten Tersupervisi</span>
                    </div>
                </div>

                {{-- Bento Card 3: Variasi Multi-Format (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center text-[#007AFF]">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Variasi Multi-Format Seketika</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Ubah 1 ide promosi menjadi 3 format sekaligus: caption feed Instagram, naskah video pendek
                        Reels/TikTok, dan pesan siaran WhatsApp yang santun.
                    </p>
                </div>

                {{-- Bento Card 4: Template Khusus Industri (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="layout-template" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Template Khusus Industri</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Struktur teks persuasif siap pakai yang disesuaikan dengan kebutuhan bisnis F&B, toko baju ritel,
                        bengkel kendaraan, dan jasa laundry.
                    </p>
                </div>

                {{-- Bento Card 5: Pustaka Aset Brand (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-teal-500/10 dark:bg-teal-500/20 flex items-center justify-center text-teal-500">
                        <i data-lucide="image" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pustaka Aset Terpusat</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Simpan logo resmi, watermark toko, dan foto resolusi tinggi produk dalam satu folder bersama agar
                        materi promosi selalu serasi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Alur Kreasi Konten Hingga Penjadwalan --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="absolute top-1/4 left-10 w-96 h-96 bg-[#007AFF]/10 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute bottom-10 right-10 w-80 h-80 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Alur Kerja Kreasi Terstruktur</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">
                        Dari Ide Produk Menjadi Materi Siap Terbit
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base mt-3">
                        Bagaimana studio kreasi COOCA mempercepat proses promosi bisnis Anda.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Pilih Produk Unggulan</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Pilih produk yang ingin dipromosikan langsung dari daftar menu kasir atau inventori gudang yang
                            memiliki stok berlimpah.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Susun Format Konten</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Pilih formula copywriting (AIDA / Storytelling) dan hasilkan variasi teks untuk Instagram Feed,
                            Story, maupun chat WhatsApp.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#00C4D8]/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Review & Persetujuan</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Staf mengirimkan draf ke pemilik bisnis. Owner memeriksa kata-kata dan visual promo, lalu
                            memberikan persetujuan dengan satu tombol.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3 backdrop-blur-sm">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Masuk Kalender Tayang</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Materi konten yang telah disetujui langsung ditempatkan pada slot tanggal dan jam tayang di
                            modul Kalender Konten COOCA.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                    Tanya Jawab Seputar Pembuatan Konten
                </p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana modul ini membantu saya membuat konten promosi produk toko?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Anda cukup memilih produk dari modul Inventori & POS. Sistem secara otomatis menarik nama barang,
                        harga, keunggulan bahan, dan foto produk, lalu menyusun alternatif teks caption promosi yang siap
                        diedit dan dijadwalkan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah teks promosi yang dihasilkan bisa disesuaikan dengan gaya bicara brand kami?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur Brand Voice (nada bicara brand)-misalnya kasual dan hangat untuk kafe kopi
                        anak muda, atau profesional dan terpercaya untuk bengkel dan klinik kecantikan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah staf atau admin konten bisa langsung menerbitkan postingan tanpa sepengetahuan
                            saya?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Tidak bisa, jika Anda mengaktifkan alur approval. Staf hanya dapat menyusun draf materi promosi ke
                        status 'Menunggu Review'. Konten hanya akan masuk ke antrean publikasi setelah Anda atau manajer
                        marketing menyetujuinya.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/60 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah materi promosi bisa langsung dibagikan ke pesan WhatsApp pelanggan?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                        Bisa. Setiap materi konten yang dibuat menyediakan format teks pendek yang ringkas dan ramah,
                        lengkap dengan link katalog belanja yang siap disebarkan ke daftar broadcast pelanggan.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] font-bold mb-1">
                        Langkah Selanjutnya
                    </h2>
                    <p class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                        Ekosistem Otomasi Konten
                    </p>
                </div>
                <a href="{{ route('public.content.calendar') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lanjut ke Kalender Konten</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                <a href="{{ route('public.content.calendar') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Kalender Konten
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Atur jadwal tayang materi promosi sebulan
                        penuh di muka.</p>
                </a>

                <a href="{{ route('public.content.publishing') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Publikasi Otomatis
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Antrean tayang multi-kanal ke media sosial
                        tanpa unggah manual.</p>
                </a>

                <a href="{{ route('public.content.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-500 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Konten
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pantau performa interaksi dan klik pesanan
                        yang dihasilkan.</p>
                </a>

                <a href="{{ route('public.omnichannel.social-media') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-[#00C4D8]/10 dark:bg-[#00C4D8]/20 text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Koneksi Media Sosial
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Hubungkan akun Instagram dan Facebook Page
                        bisnis Anda.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Dual ambient glows inside CTA --}}
                <div
                    class="absolute top-0 right-10 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-10 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Mulai Hasilkan Konten Promosi Produk Berkualitas
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Bebaskan tim pemasaran Anda dari kebingungan ide konten harian dan ciptakan materi promosi yang
                        terhubung dengan stok produk Anda bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all">
                            Coba Demo Studio Konten
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all">
                            Konsultasi Kebutuhan Konten
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
