@extends('layouts.public_marketing')

@section('title', 'Pusat Bantuan & Panduan Resmi COOCA: Solusi Cepat untuk Bisnis Anda')
@section('description', 'Pusat bantuan, panduan operasional kasir, printer Bluetooth, manajemen stok, dan kontak technical support resmi COOCA Indonesia.')
@section('keywords', 'cooca support, bantuan cooca, panduan cooca, call center cooca, technical support kasir, cara pakai cooca pos')

@push('seo')
    <link rel="canonical" href="{{ route('public.support') }}">
    <meta property="og:title" content="Pusat Bantuan & Panduan Resmi COOCA: Solusi Cepat untuk Bisnis Anda">
    <meta property="og:description" content="Temukan solusi cepat untuk operasional kasir, inventaris, koneksi printer thermal, dan pembukuan toko Anda.">
    <meta property="og:url" content="{{ route('public.support') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Pusat Bantuan & Panduan Resmi COOCA">
    <meta name="twitter:description" content="Pusat dokumentasi dan layanan pelanggan 24/7 COOCA untuk kelancaran bisnis Anda.">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      "name": "Pusat Bantuan COOCA",
      "description": "Layanan bantuan teknis dan panduan operasional sistem operasi bisnis COOCA.",
      "url": "{{ route('public.support') }}"
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
                <span>Bantuan</span>
                <span aria-hidden="true">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold" aria-current="page">Pusat Bantuan</span>
            </nav>

            {{-- Hero Section (2-Col Desktop) --}}
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                {{-- Left: Headline & Support Contact --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 dark:bg-blue-400/15 border border-blue-500/20 text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="headphones" class="w-3.5 h-3.5" aria-hidden="true"></i>
                        <span>Layanan Pendampingan Bisnis</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-[3rem] font-bold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Ada Pertanyaan Teknis? Tim Kami Siap Mendampingi Anda
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Kami memahami bahwa kelancaran kasir dan pembukuan adalah denyut nadi toko Anda. Temukan jawaban dari panduan tertulis atau hubungi konsultan technical support kami secara langsung.
                    </p>

                    {{-- Escalation Channels --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Respon WhatsApp langsung oleh tim manusia</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Panduan konfigurasi printer thermal Bluetooth</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Bantuan migrasi data produk dari Excel</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0" aria-hidden="true"></i>
                            <span>Sesi privat training online untuk staf gerai</span>
                        </div>
                    </div>

                    {{-- Direct Action Buttons --}}
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20tim%20Support%20COOCA,%20saya%20membutuhkan%20bantuan" target="_blank" rel="noopener" class="h-12 px-7 rounded-[14px] bg-[#34C759] hover:bg-[#2DB04D] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="message-circle" class="w-4 h-4" aria-hidden="true"></i>
                            <span>Chat WhatsApp Bantuan</span>
                        </a>
                        <a href="{{ route('public.demo') }}" class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 text-[#1D1D1F] dark:text-[#F5F5F7] hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-sm font-semibold flex items-center justify-center gap-2 shadow-sm transition active:scale-[0.98]">
                            <i data-lucide="play" class="w-4 h-4 text-[#007AFF]" aria-hidden="true"></i>
                            <span>Coba Demo Interaktif</span>
                        </a>
                    </div>
                </div>

                {{-- Right: Support Desk Card --}}
                <div class="lg:col-span-5">
                    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800/80 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-mono font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kanal Dukungan Resmi</span>
                            </div>
                            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-500/10 px-2.5 py-0.5 rounded-full">
                                Tim Online
                            </span>
                        </div>

                        {{-- Channels Deck --}}
                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-1">
                                <div class="flex justify-between items-center font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759]"></i>
                                        WhatsApp Customer Care
                                    </span>
                                    <span class="font-mono text-emerald-600">Aktif</span>
                                </div>
                                <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Respon cepat setiap hari: 08:00 - 22:00 WIB</p>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-1">
                                <div class="flex justify-between items-center font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="mail" class="w-4 h-4 text-[#007AFF]"></i>
                                        Email Support
                                    </span>
                                    <span class="font-mono text-[#007AFF]">support@cooca.id</span>
                                </div>
                                <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Untuk eskalasi kendala akun dan kemitraan</p>
                            </div>

                            <div class="p-3.5 rounded-[14px] bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800 space-y-1">
                                <div class="flex justify-between items-center font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">
                                    <span class="flex items-center gap-2">
                                        <i data-lucide="activity" class="w-4 h-4 text-emerald-500"></i>
                                        Status Server Cloud
                                    </span>
                                    <span class="font-mono text-emerald-600">100% Operational</span>
                                </div>
                                <p class="text-[11px] text-[#6E6E73] dark:text-[#86868B]">Server database, API payment, & POS aktif normal</p>
                            </div>
                        </div>

                        {{-- Quick Help Note --}}
                        <div class="p-3 rounded-[12px] bg-blue-500/10 text-[#007AFF] text-xs font-semibold text-center">
                            Dukungan tersedia untuk seluruh pengguna COOCA tanpa biaya tambahan.
                        </div>
                    </div>
                </div>
            </section>

            {{-- 6 Topic Categories Bento Grid --}}
            <section class="space-y-8">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">
                        Topik Panduan
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                        Cari Solusi Berdasarkan Modul Sistem
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-blue-500/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kasir POS & Transaksi</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Panduan proses order, pembayaran tunai/QRIS, pembatalan pesanan (void), split bill meja, dan penerbitan nota belanja.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="boxes" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Inventaris & Stok Opname</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Cara import produk massal via Excel, atur satuan bertingkat (dus ke pcs), atur resep bahan baku (BOM), dan transfer antar cabang.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-amber-500/10 text-amber-600 flex items-center justify-center">
                            <i data-lucide="printer" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Koneksi Printer & Perangkat</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Langkah pairing printer thermal Bluetooth 58mm/80mm di Android, setting printer kabel LAN dapur, dan barcode scanner USB.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-purple-500/10 text-purple-600 flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">WhatsApp & Notifikasi Otomatis</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Konfigurasi pengiriman nota WhatsApp, pesan pengingat booking janji temu, dan notifikasi cucian laundry siap jemput.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-500/10 text-rose-600 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Laporan Keuangan & Kasir</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Cara membaca laporan laba rugi, rekap selisih uang laci saat tutup shift, pembagian komisi montir/terapis, dan ekspor PDF.
                        </p>
                    </div>

                    <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 space-y-3 shadow-sm">
                        <div class="w-10 h-10 rounded-[12px] bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Akun, Staf & Cabang Baru</h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            Menambah staf kasir dengan pembatasan hak akses, reset kata sandi, dan panduan menambahkan cabang outlet bisnis baru.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Support FAQs --}}
            <section class="space-y-6 max-w-3xl mx-auto">
                <div class="text-center space-y-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-[#007AFF] dark:text-[#0A84FF] block">Tanya Jawab Bantuan</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Pertanyaan Sering Diajukan Seputar Bantuan</h2>
                </div>

                <div class="space-y-3.5">
                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Bagaimana jika printer kasir Bluetooth saya tidak terdeteksi di aplikasi?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Pastikan printer thermal dalam kondisi menyala dan sudah di-pairing terlebih dahulu di menu Pengaturan Bluetooth smartphone atau tablet Anda. Setelah itu, buka COOCA > Pengaturan > Perangkat Keras, lalu pilih printer yang telah terhubung.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Apakah data toko saya tetap aman jika perangkat kasir saya hilang atau rusak?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Sangat aman. Seluruh data transaksi, pelanggan, dan stok tersimpan di server cloud terenkripsi. Jika HP atau laptop kasir Anda hilang, Anda cukup login dari perangkat baru dan seluruh data bisnis Anda akan langsung kembali utuh seketika.
                        </p>
                    </details>

                    <details class="group bg-white dark:bg-[#1C1C1E] border border-neutral-200/80 dark:border-neutral-800 rounded-[18px] p-5 transition-all">
                        <summary class="flex justify-between items-center cursor-pointer font-bold text-sm sm:text-base text-[#1D1D1F] dark:text-[#F5F5F7]">
                            <span>Berapa lama waktu respon tim customer service WhatsApp?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#6E6E73] group-open:rotate-180 transition-transform" aria-hidden="true"></i>
                        </summary>
                        <p class="mt-3 text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 pt-3">
                            Tim support kami memprioritaskan kendala operasional kasir dengan respon rata-rata di bawah 5 menit pada jam kerja aktif (08:00 - 22:00 WIB) setiap hari termasuk akhir pekan.
                        </p>
                    </details>
                </div>
            </section>

            {{-- Final CTA --}}
            <section class="p-8 sm:p-12 rounded-[24px] bg-[#161618] border border-white/[0.08] text-white text-center space-y-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#86868B] inline-flex items-center gap-1.5 mx-auto">
                    <i data-lucide="headphones" class="w-4 h-4 text-[#34C759]" aria-hidden="true"></i>
                    <span>Kami Siap Membantu Perkembangan Usaha Anda</span>
                </div>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Butuh Bantuan Langsung? Hubungi Kami Sekarang
                </h3>
                <p class="text-sm text-[#86868B] max-w-xl mx-auto leading-relaxed">
                    Jangan ragu berkonsultasi mengenai kebutuhan sistem untuk kafe, toko, bengkel, atau pabrik Anda bersama tim kami.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                    <a href="{{ \App\Models\SystemSetting::get('social_whatsapp_url', 'https://wa.me/6285287864176') }}?text=Halo%20tim%20COOCA" target="_blank" rel="noopener" class="h-12 px-8 rounded-[14px] bg-[#34C759] hover:bg-[#2DB04D] text-white font-semibold text-sm inline-flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        <i data-lucide="message-circle" class="w-4 h-4" aria-hidden="true"></i>
                        <span>Chat WhatsApp Support</span>
                    </a>
                    <a href="{{ route('public.pricing') }}" class="h-12 px-7 rounded-[14px] bg-white/10 hover:bg-white/15 text-white font-semibold text-sm inline-flex items-center gap-2 transition-all">
                        <span>Lihat Paket Berlangganan</span>
                    </a>
                </div>
            </section>

        </div>
    </div>
@endsection
