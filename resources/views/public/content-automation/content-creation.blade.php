@extends('layouts.public_marketing')

@section('title', 'Software Pembuatan Konten Promosi & Workflow Kreasi Bisnis | COOCA')
@section('description', 'Transformasikan produk toko fisik Anda menjadi materi promosi media sosial yang memikat.
    Workflow kreasi konten terpadu: copywriting multi-format, visual produk, dan persetujuan draf sebelum tayang.')
@section('keywords', 'software pembuatan konten promosi, workflow kreasi konten bisnis, generator materi promosi produk,
    copywriting katalog toko, studio konten umkm')

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
        "text": "Bisa. Anda dapat mengatur Brand Voice (nada bicara brand)—misalnya kasual dan hangat untuk kafe kopi anak muda, atau profesional dan terpercaya untuk bengkel dan klinik kecantikan."
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
    <div class="relative overflow-hidden bg-white dark:bg-black transition-colors duration-300">

        {{-- Ambient Light Accent --}}
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1300px] h-[480px] bg-gradient-to-b from-pink-500/10 via-purple-500/5 to-transparent blur-3xl pointer-events-none -z-10">
        </div>

        {{-- Breadcrumb --}}
        <nav class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li><span class="text-neutral-500 dark:text-neutral-400">Content Automation</span></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Pembuatan Konten
                    Promosi</li>
            </ol>
        </nav>

        {{-- 1. HERO SECTION (2 Columns) --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-pink-50 dark:bg-pink-950/60 border border-pink-200/60 dark:border-pink-800/40 text-pink-700 dark:text-pink-400 text-xs font-semibold tracking-wide">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Product-Driven Content Creation Studio</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                        Ubah Produk & Menu Toko Menjadi <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600">Konten
                            Promosi yang Menjual</span>
                    </h1>

                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                        Berhenti membuang waktu berjam-jam memikirkan ide postingan media sosial dari nol. Pilih produk
                        langsung dari katalog toko Anda, susun variasi pesan untuk Instagram, TikTok, dan WhatsApp, lalu
                        tinjau draf bersama tim sebelum otomatis dipublikasikan.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                            <span>Coba Studio Konten Sekarang</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.content.calendar') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                            <span>Lihat Kalender Konten</span>
                        </a>
                    </div>

                    {{-- Key Trust Specs --}}
                    <div
                        class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Sumber Konten</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Katalog Stok Riil</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Format Keluaran</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Feed, Story, & Chat</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Kendali Kualitas</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Alur Review Draf</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live Content Creation Studio UI --}}
                <div class="lg:col-span-6">
                    <div
                        class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">

                        {{-- Studio Top Bar --}}
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-pink-500/20 text-pink-400">
                                    <i data-lucide="pen-tool" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-neutral-200">Studio Pembuatan Konten Produk</div>
                                    <div class="text-[10px] text-neutral-500">Sumber: Katalog POS • SKU-112</div>
                                </div>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 text-[10px] font-mono font-bold">Status:
                                Draf Siap Review</span>
                        </div>

                        {{-- Product Source Selector Pill --}}
                        <div
                            class="p-2.5 my-3 rounded-xl bg-neutral-950/80 border border-neutral-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-lg bg-neutral-800 flex items-center justify-center text-amber-400 shrink-0">
                                    <i data-lucide="coffee" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-white text-[11px]">Croissant Butter Fresh Oven</div>
                                    <div class="text-[10px] text-neutral-400 font-mono">Rp 28.000 • Stok Toko Sudirman: 15
                                        pcs</div>
                                </div>
                            </div>
                            <span class="text-[10px] text-pink-400 font-medium">Ganti Produk</span>
                        </div>

                        {{-- Multi-Channel Variant Generator Preview --}}
                        <div class="space-y-2 text-xs text-left">
                            <div class="text-[10px] uppercase font-mono text-neutral-500 px-1">Variasi Materi Pemasaran:
                            </div>

                            {{-- Variant 1: Instagram Feed --}}
                            <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 space-y-1">
                                <div class="flex items-center justify-between text-[10px]">
                                    <span class="font-bold text-pink-400 flex items-center gap-1">
                                        <i data-lucide="instagram" class="w-3 h-3"></i> Instagram Feed & Caption
                                    </span>
                                    <span class="text-neutral-500">Tone: Hangat & Menggoda</span>
                                </div>
                                <p class="text-[11px] text-neutral-300 leading-relaxed font-sans">
                                    "Renyah di gigitan pertama, lembut dan wangi butter di dalam! 🥐 Temani secangkir kopi
                                    pagimu di Sudirman & Senopati. Dibuat fresh setiap pagi untuk harimu yang produktif..."
                                </p>
                            </div>

                            {{-- Variant 2: WhatsApp Promo Broadcast --}}
                            <div class="p-2.5 rounded-xl bg-neutral-950/60 border border-neutral-800/80 space-y-1">
                                <div class="flex items-center justify-between text-[10px]">
                                    <span class="font-bold text-emerald-400 flex items-center gap-1">
                                        <i data-lucide="message-circle" class="w-3 h-3"></i> Pesan Siaran WhatsApp
                                    </span>
                                    <span class="text-neutral-500">Format Singkat Ramah</span>
                                </div>
                                <p class="text-[11px] text-neutral-300 leading-relaxed font-sans">
                                    "Halo Kak! Promo kilat pagi ini: Beli 2 Croissant Butter gratis 1 Americano sebelum jam
                                    11:00. Tunjukkan pesan ini ke kasir ya! ✨"
                                </p>
                            </div>
                        </div>

                        {{-- Review & Approval Footer Action --}}
                        <div class="mt-3 pt-2.5 border-t border-neutral-800 flex items-center justify-between text-xs">
                            <span class="text-neutral-400 text-[10px]">Disusun oleh: Admin Sarah (10:15 WIB)</span>
                            <div class="flex items-center gap-2">
                                <button
                                    class="px-2.5 py-1 rounded bg-neutral-800 text-neutral-300 text-[10px] hover:bg-neutral-700">Revisi</button>
                                <button
                                    class="px-3 py-1 rounded bg-pink-600 hover:bg-pink-500 text-white font-bold text-[10px] transition-colors">
                                    Setujui & Jadwalkan
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        {{-- 2. PAIN POINTS: Kerumitan Membuat Konten Secara Manual --}}
        <section
            class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-3">
                        Tantangan Pembuatan Konten</h2>
                    <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Mengapa Tim Pemasaran Anda Sering Kehabisan Ide Konten Promosi?
                    </p>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                        Banyak bisnis memiliki produk unggulan yang hebat, namun kesulitan mengomunikasikan nilainya di
                        media sosial secara teratur karena alur pembuatan materi yang rumit.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Writer's Block Setiap Hari</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Menghabiskan 2 jam setiap pagi hanya untuk menatap layar kosong dan bingung harus menulis
                            caption apa untuk mempromosikan produk toko hari ini.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="folder-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Foto & Data Produk Tercecer</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Foto produk tersimpan di Google Drive, harga ada di kasir POS, dan spesifikasi bahan ada di
                            catatan dapur. Proses mengumpulkan bahan memakan waktu seharian.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="message-square-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Salah Tulis Harga & Promo</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
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
                <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-3">Fitur
                    Studio Kreasi COOCA</h2>
                <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Alur Kerja Konten Profesional Tanpa Biaya Agensi
                </p>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                    Memadukan data fisik produk toko dengan kemudahan menyusun pesan promosi yang persuasif.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Product-Driven Drafting (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-pink-100 dark:bg-pink-950 flex items-center justify-center text-pink-600 dark:text-pink-400">
                            <i data-lucide="box" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Kreasi Konten Langsung dari Produk Nyata
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Cukup pilih item dari sistem POS atau stok gudang Anda. Sistem langsung menyajikan foto produk
                            resmi, harga asli, keunggulan bahan baku, dan tautan belanja yang tervalidasi tanpa risiko salah
                            ketik harga.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                        <span class="text-neutral-600 dark:text-neutral-300 font-medium">Validasi Harga:</span>
                        <span class="text-pink-600 dark:text-pink-400 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Harga promosi selalu sesuai dengan sistem kasir
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Alur Persetujuan Tim (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="check-square" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Alur Persetujuan & Koreksi Draf
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Staf konten menyiapkan draf teks dan materi visual, sementara pemilik bisnis atau manajer
                            marketing dapat menyetujui atau menambahkan catatan revisi sebelum jadwal tayang.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                        <span class="text-neutral-500">Kualitas Terjaga</span>
                        <span class="text-purple-500 font-bold">100% Konten Tersupervisi</span>
                    </div>
                </div>

                {{-- Bento Card 3: Variasi Multi-Format (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Variasi Multi-Format Seketika</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Ubah 1 ide promosi menjadi 3 format sekaligus: caption feed Instagram, naskah video pendek
                        Reels/TikTok, dan pesan siaran WhatsApp yang santun.
                    </p>
                </div>

                {{-- Bento Card 4: Template Khusus Industri (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="layout-template" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Template Khusus Industri</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Struktur teks persuasif siap pakai yang disesuaikan dengan kebutuhan bisnis F&B, toko baju ritel,
                        bengkel kendaraan, dan jasa laundry.
                    </p>
                </div>

                {{-- Bento Card 5: Pustaka Aset Brand (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i data-lucide="image" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Pustaka Aset Terpusat</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Simpan logo resmi, watermark toko, dan foto resolusi tinggi produk dalam satu folder bersama agar
                        materi promosi selalu serasi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Alur Kreasi Konten Hingga Penjadwalan --}}
        <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 text-pink-400 text-xs font-semibold mb-3 border border-pink-500/30">
                        <span>Alur Kerja Kreasi Terstruktur</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Dari Ide Produk Menjadi Materi Siap Terbit
                    </h2>
                    <p class="text-neutral-400 text-sm sm:text-base mt-3">
                        Bagaimana studio kreasi COOCA mempercepat proses promosi bisnis Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-pink-600/30 text-pink-400 flex items-center justify-center font-bold text-xs border border-pink-500/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Pilih Produk Unggulan</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Pilih produk yang ingin dipromosikan langsung dari daftar menu kasir atau inventori gudang yang
                            memiliki stok berlimpah.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Susun Format Konten</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Pilih formula copywriting (AIDA / Storytelling) dan hasilkan variasi teks untuk Instagram Feed,
                            Story, maupun chat WhatsApp.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-indigo-600/30 text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Review & Persetujuan</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Staf mengirimkan draf ke pemilik bisnis. Owner memeriksa kata-kata dan visual promo, lalu
                            memberikan persetujuan dengan satu tombol.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Masuk Kalender Tayang</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
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
                <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-2">
                    Pertanyaan Umum</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Pembuatan
                    Konten</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana modul ini membantu saya membuat konten promosi produk toko?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Anda cukup memilih produk dari modul Inventori & POS. Sistem secara otomatis menarik nama barang,
                        harga, keunggulan bahan, dan foto produk, lalu menyusun alternatif teks caption promosi yang siap
                        diedit dan dijadwalkan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah teks promosi yang dihasilkan bisa disesuaikan dengan gaya bicara brand kami?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur Brand Voice (nada bicara brand)—misalnya kasual dan hangat untuk kafe kopi
                        anak muda, atau profesional dan terpercaya untuk bengkel dan klinik kecantikan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah staf atau admin konten bisa langsung menerbitkan postingan tanpa sepengetahuan
                            saya?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Tidak bisa, jika Anda mengaktifkan alur approval. Staf hanya dapat menyusun draf materi promosi ke
                        status 'Menunggu Review'. Konten hanya akan masuk ke antrean publikasi setelah Anda atau manajer
                        marketing menyetujuinya.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah materi promosi bisa langsung dibagikan ke pesan WhatsApp pelanggan?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Setiap materi konten yang dibuat menyediakan format teks pendek yang ringkas dan ramah,
                        lengkap dengan link katalog belanja yang siap disebarkan ke daftar broadcast pelanggan.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-pink-600 dark:text-pink-400 font-semibold mb-1">
                        Langkah Selanjutnya</h2>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Otomasi Konten</p>
                </div>
                <a href="{{ route('public.content.calendar') }}"
                    class="text-xs sm:text-sm font-semibold text-pink-600 dark:text-pink-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lanjut ke Kalender Konten</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.content.calendar') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">
                        Kalender Konten</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Atur jadwal tayang materi promosi
                        sebulan penuh di muka.</p>
                </a>

                <a href="{{ route('public.content.publishing') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">
                        Publikasi Otomatis</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Antrean tayang multi-kanal ke media
                        sosial tanpa unggah manual.</p>
                </a>

                <a href="{{ route('public.content.analytics') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">
                        Analitik Konten</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Pantau performa interaksi dan klik
                        pesanan yang dihasilkan.</p>
                </a>

                <a href="{{ route('public.omnichannel.social-media') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-pink-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-pink-600 transition-colors">
                        Koneksi Media Sosial</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Hubungkan akun Instagram dan Facebook
                        Page bisnis Anda.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Mulai Hasilkan Konten Promosi Produk Berkualitas
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-400">
                        Bebaskan tim pemasaran Anda dari kebingungan ide konten harian dan ciptakan materi promosi yang
                        terhubung dengan stok produk Anda bersama COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-pink-600 hover:bg-pink-500 text-white font-semibold text-sm transition-all shadow-md">
                            Coba Demo Studio Konten
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                            Konsultasi Kebutuhan Konten
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
