@extends('layouts.public_marketing')

@section('title', 'Pusat Bantuan & Panduan Resmi COOCA: Solusi Cepat untuk Bisnis Anda')
@section('description', 'Pusat bantuan, panduan operasional kasir, printer Bluetooth, manajemen stok, dan kontak
    technical support resmi COOCA Indonesia.')
@section('keywords', 'cooca support, bantuan cooca, panduan cooca, call center cooca, technical support kasir, cara
    pakai cooca pos')

    @push('seo')
        <link rel="canonical" href="{{ route('public.support') }}">
        <meta property="og:title" content="Pusat Bantuan & Panduan Resmi COOCA: Solusi Cepat untuk Bisnis Anda">
        <meta property="og:description"
            content="Temukan solusi cepat untuk operasional kasir, inventaris, koneksi printer thermal, dan pembukuan toko Anda.">
        <meta property="og:url" content="{{ route('public.support') }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Pusat Bantuan & Panduan Resmi COOCA">
        <meta name="twitter:description"
            content="Pusat dokumentasi dan layanan pelanggan 24/7 COOCA untuk kelancaran bisnis Anda.">

        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "ContactPage",
      "name": "Pusat Bantuan COOCA",
      "description": "Layanan bantuan teknis dan panduan operasional sistem operasi bisnis COOCA.",
      "url": "{{ route('public.support') }}"
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- HERO SECTION (Midnight #060B1E Full-Bleed) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section
            class="relative bg-[#060B1E] text-white pt-10 sm:pt-14 pb-16 lg:pb-24 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span>Bantuan</span>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Pusat Bantuan</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {{-- Left: Headline & Support Contact --}}
                    <div class="lg:col-span-7 space-y-6">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-sm">
                            <i data-lucide="headphones" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            <span>Layanan Pendampingan Bisnis</span>
                        </div>

                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.75rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                            Ada Pertanyaan Teknis? <span class="text-[#00C4D8]">Tim Kami Siap Mendampingi Anda</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl text-pretty">
                            Kami memahami bahwa kelancaran kasir dan pembukuan adalah denyut nadi toko Anda. Temukan jawaban
                            dari panduan tertulis atau hubungi konsultan technical support kami secara langsung.
                        </p>

                        {{-- Escalation Channels --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Respon WhatsApp langsung oleh tim manusia</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Panduan konfigurasi printer thermal Bluetooth</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Bantuan migrasi data produk dari Excel</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs sm:text-sm font-semibold text-slate-200">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#00C4D8] shrink-0 mt-0.5"
                                    aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 leading-snug">Sesi privat training online untuk staf gerai</span>
                            </div>
                        </div>

                        {{-- Direct Action Buttons --}}
                        <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                            <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20tim%20Support%20COOCA,%20saya%20membutuhkan%20bantuan"
                                target="_blank" rel="noopener"
                                class="h-12 px-7 rounded-[14px] bg-[#34C759] hover:bg-[#2DB04D] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/25 active:scale-[0.98] transition-all">
                                <i data-lucide="message-circle" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                                <span>Chat WhatsApp Bantuan</span>
                            </a>
                            <a href="{{ route('public.demo') }}"
                                class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98] backdrop-blur-sm">
                                <i data-lucide="play" class="w-4 h-4 text-[#00C4D8] shrink-0" aria-hidden="true"></i>
                                <span>Coba Demo Interaktif</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right: Support Desk Card --}}
                    <div class="lg:col-span-5">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-5">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse shrink-0"></span>
                                    <span class="text-xs font-mono font-bold text-white truncate">Kanal Dukungan Resmi</span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#00C4D8] bg-[#00C4D8]/15 border border-[#00C4D8]/30 px-2.5 py-0.5 rounded-full shrink-0">
                                    Tim Online
                                </span>
                            </div>

                            {{-- Channels Deck --}}
                            <div class="space-y-3 text-xs">
                                <div class="p-3.5 rounded-[14px] bg-[#060B1E]/60 border border-white/10 space-y-1">
                                    <div class="flex justify-between items-center font-bold text-white gap-2">
                                        <span class="flex items-center gap-2 min-w-0">
                                            <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                            <span class="truncate">WhatsApp Customer Care</span>
                                        </span>
                                        <span class="font-mono text-emerald-400 shrink-0">Aktif</span>
                                    </div>
                                    <p class="text-[11px] text-slate-300">Respon cepat setiap hari: 08:00 - 22:00 WIB</p>
                                </div>

                                <div class="p-3.5 rounded-[14px] bg-[#060B1E]/60 border border-white/10 space-y-1">
                                    <div class="flex justify-between items-center font-bold text-white gap-2">
                                        <span class="flex items-center gap-2 min-w-0">
                                            <i data-lucide="mail" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                            <span class="truncate">Email Support</span>
                                        </span>
                                        <span class="font-mono text-[#00C4D8] shrink-0">support@cooca.id</span>
                                    </div>
                                    <p class="text-[11px] text-slate-300">Untuk eskalasi kendala akun dan kemitraan</p>
                                </div>

                                <div class="p-3.5 rounded-[14px] bg-[#060B1E]/60 border border-white/10 space-y-1">
                                    <div class="flex justify-between items-center font-bold text-white gap-2">
                                        <span class="flex items-center gap-2 min-w-0">
                                            <i data-lucide="activity" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                            <span class="truncate">Status Server Cloud</span>
                                        </span>
                                        <span class="font-mono text-emerald-400 shrink-0">100% Operational</span>
                                    </div>
                                    <p class="text-[11px] text-slate-300">Server database, API payment, & POS aktif normal
                                    </p>
                                </div>
                            </div>

                            {{-- Quick Help Note --}}
                            <div
                                class="p-3 rounded-[12px] bg-[#007AFF]/15 border border-[#007AFF]/30 text-[#00C4D8] text-xs font-semibold text-center leading-snug">
                                Dukungan tersedia untuk seluruh pengguna COOCA tanpa biaya tambahan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content Sections --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 space-y-16 sm:space-y-24">

            {{-- 6 Topic Categories Bento Grid --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">
                        Topik Panduan
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.2] text-balance break-words">
                        Cari Solusi Berdasarkan Modul Sistem
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="shopping-cart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Kasir POS & Transaksi</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Panduan proses order, pembayaran tunai/QRIS, pembatalan pesanan (void), split bill meja, dan
                            penerbitan nota belanja.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="boxes" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Inventaris & Stok Opname</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Cara import produk massal via Excel, atur satuan bertingkat (dus ke pcs), atur resep bahan baku
                            (BOM), dan transfer antar cabang.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="printer" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Koneksi Printer & Perangkat</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Langkah pairing printer thermal Bluetooth 58mm/80mm di Android, setting printer kabel LAN dapur,
                            dan barcode scanner USB.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">WhatsApp & Notifikasi Otomatis</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Konfigurasi pengiriman nota WhatsApp, pesan pengingat booking janji temu, dan notifikasi cucian
                            laundry siap jemput.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Laporan Keuangan & Kasir</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Cara membaca laporan laba rugi, rekap selisih uang laci saat tutup shift, pembagian komisi
                            montir/terapis, dan ekspor PDF.
                        </p>
                    </div>

                    <div
                        class="p-6 rounded-[22px] bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-3 shadow-sm">
                        <div
                            class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug text-balance break-words">Akun, Staf & Cabang Baru</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty">
                            Menambah staf kasir dengan pembatasan hak akses, reset kata sandi, dan panduan menambahkan
                            cabang outlet bisnis baru.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Support FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#00C4D8] block">Tanya
                        Jawab Bantuan</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white leading-[1.2] text-balance break-words">Pertanyaan Sering Diajukan
                        Seputar Bantuan</h2>
                </div>

                <div class="space-y-3.5">
                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Bagaimana jika printer kasir Bluetooth saya tidak terdeteksi di aplikasi?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Pastikan printer thermal dalam kondisi menyala dan sudah di-pairing terlebih dahulu di menu
                            Pengaturan Bluetooth smartphone atau tablet Anda. Setelah itu, buka COOCA > Pengaturan >
                            Perangkat Keras, lalu pilih printer yang telah terhubung.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Apakah data toko saya tetap aman jika perangkat kasir saya hilang atau rusak?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Sangat aman. Seluruh data transaksi, pelanggan, dan stok tersimpan di server cloud terenkripsi.
                            Jika HP atau laptop kasir Anda hilang, Anda cukup login dari perangkat baru dan seluruh data
                            bisnis Anda akan langsung kembali utuh seketika.
                        </p>
                    </details>

                    <details
                        class="group bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 rounded-[18px] p-5 transition-all">
                        <summary
                            class="flex justify-between items-center gap-3 cursor-pointer font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                            <span class="min-w-0 flex-1 leading-snug">Berapa lama waktu respon tim customer service WhatsApp?</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform shrink-0"
                                aria-hidden="true"></i>
                        </summary>
                        <p
                            class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed text-pretty border-t border-slate-100 dark:border-white/10 pt-3">
                            Tim support kami memprioritaskan kendala operasional kasir dengan respon rata-rata di bawah 5
                            menit pada jam kerja aktif (08:00 - 22:00 WIB) setiap hari termasuk akhir pekan.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Final CTA --}}
            <section
                class="relative p-8 sm:p-12 rounded-[24px] bg-[#060B1E] border border-white/10 text-white text-center space-y-5 shadow-sm overflow-hidden">
                <div
                    class="absolute top-0 right-1/4 w-72 h-72 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-1/4 w-72 h-72 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="relative z-10 space-y-5 max-w-2xl mx-auto">
                    <div
                        class="text-xs font-semibold uppercase tracking-wider text-[#00C4D8] inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/15 border border-[#00C4D8]/30">
                        <i data-lucide="headphones" class="w-4 h-4 text-[#00C4D8]" aria-hidden="true"></i>
                        <span>Kami Siap Membantu Perkembangan Usaha Anda</span>
                    </div>
                    <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-[1.2] text-balance break-words">
                        Butuh Bantuan Langsung? Hubungi Kami Sekarang
                    </h3>
                    <p class="text-sm text-slate-300 leading-relaxed text-pretty">
                        Jangan ragu berkonsultasi mengenai kebutuhan sistem untuk kafe, toko, bengkel, atau pabrik Anda
                        bersama tim kami.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20tim%20COOCA"
                            target="_blank" rel="noopener"
                            class="h-12 px-8 rounded-[14px] bg-[#34C759] hover:bg-[#2DB04D] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-lg shadow-emerald-500/25 active:scale-95 transition-all">
                            <i data-lucide="message-circle" class="w-4 h-4 shrink-0" aria-hidden="true"></i>
                            <span>Chat WhatsApp Support</span>
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all backdrop-blur-sm">
                            <span>Lihat Paket Berlangganan</span>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
