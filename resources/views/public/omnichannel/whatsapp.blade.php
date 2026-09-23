@extends('layouts.public_marketing')

@section('title', 'Software Notifikasi WhatsApp Bisnis, Struk Kasir & Invoice Digital | COOCA')
@section('description', 'Hubungkan sistem operasional bisnis Anda dengan WhatsApp. Kirim struk kasir digital otomatis,
    notifikasi status pesanan, update pengerjaan servis, dan pengingat invoice langsung ke chat pelanggan.')
@section('keywords', 'software notifikasi whatsapp bisnis, kirim struk kasir via wa, invoice digital whatsapp,
    notifikasi pesanan wa umkm, reminder tagihan whatsapp')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA WhatsApp Business Integration",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Modul komunikasi WhatsApp terintegrasi untuk pengiriman nota transaksi digital, konfirmasi pesanan, dan layanan pelanggan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Pengiriman struk kasir POS digital otomatis via link pesan WhatsApp",
    "Notifikasi status pesanan siap ambil atau pengerjaan servis selesai",
    "Pengingat jatuh tempo invoice tagihan resmi langsung ke kontak klien",
    "Pemberitahuan reward poin loyalitas dan ucapan promo personal",
    "Komunikasi transaksional tanpa perlu simpan nomor pelanggan di kontak pribadi staf"
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
      "name": "Bagaimana cara kerja pengiriman struk kasir lewat WhatsApp di COOCA?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Saat transaksi selesai di layar kasir POS, kasir cukup memasukkan nomor WhatsApp pelanggan dan menekan tombol 'Kirim Nota WA'. Sistem menyiapkan pesan teks sopan dengan tautan struk digital resmi yang langsung dapat dibuka oleh pelanggan tanpa aplikasi tambahan."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah staf toko harus menyimpan nomor telepon pelanggan ke kontak HP mereka?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sama sekali tidak. Pengiriman notifikasi dilakukan langsung melalui sistem COOCA menggunakan nomor resmi bisnis Anda. Nomor kontak pelanggan tersimpan aman di database perusahaan dan tidak bocor ke perangkat pribadi staf."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah pesan WhatsApp ini aman dari pemblokiran (banned)?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "COOCA mengutamakan komunikasi transaksional nyata yang memang diharapkan oleh pelanggan—seperti konfirmasi struk pembelian, status cucian selesai, atau nota servis kendaraan. Kami tidak memfasilitasi pesan spam massal tanpa izin sehingga reputasi nomor bisnis Anda tetap terjaga sehat."
      }
    },
    {
      "@type": "Question",
      "name": "Bisakah template pesan teks disesuaikan dengan gaya bahasa brand kami?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bisa. Anda dapat mengatur template sapaan, nama toko, ucapan terima kasih, dan menyisipkan variabel dinamis seperti nama pelanggan, nomor nota, total belanja, serta saldo poin loyalitas."
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
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1300px] h-[480px] bg-gradient-to-b from-emerald-500/10 via-green-500/5 to-transparent blur-3xl pointer-events-none -z-10">
        </div>

        {{-- Breadcrumb --}}
        <nav class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                <li><a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Home</a></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li><span class="text-neutral-500 dark:text-neutral-400">Omnichannel</span></li>
                <li><i data-lucide="chevron-right" class="w-3 h-3 text-neutral-400"></i></li>
                <li class="text-neutral-900 dark:text-neutral-200 font-semibold" aria-current="page">Integrasi Komunikasi
                    WhatsApp</li>
            </ol>
        </nav>

        {{-- 1. HERO SECTION (2 Columns) --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20 lg:pt-12 lg:pb-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                {{-- Left Column: Copy & Value Proposition --}}
                <div class="lg:col-span-6 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-semibold tracking-wide">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        <span>Transactional WhatsApp & Digital Receipts</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-neutral-900 dark:text-white leading-[1.15]">
                        Hubungkan Operasional Toko Langsung ke <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-600 to-green-500">WhatsApp
                            Pelanggan</span>
                    </h1>

                    <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
                        Kirimkan struk kasir digital tanpa kertas thermal, beri tahu pelanggan saat pesanan siap diambil,
                        infokan status pengerjaan servis, dan tagih invoice jatuh tempo langsung ke aplikasi chat yang
                        dibuka pelanggan setiap hari.
                    </p>

                    {{-- Action CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                        <a href="{{ route('public.demo') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition-all duration-200">
                            <span>Coba Demo Notifikasi WA</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('public.erp.pos') }}"
                            class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-neutral-100 dark:bg-neutral-800/90 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-semibold text-sm border border-neutral-200/80 dark:border-neutral-700/80 transition-all">
                            <span>Koneksi ke Kasir POS</span>
                        </a>
                    </div>

                    {{-- Key Trust Specs --}}
                    <div
                        class="pt-4 border-t border-neutral-100 dark:border-neutral-800/80 grid grid-cols-3 gap-4 text-left">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Bentuk Struk</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Link Digital Resmi</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Pengiriman Pesan</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Tanpa Simpan Nomor</div>
                        </div>
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Sifat Komunikasi</div>
                            <div class="text-sm font-bold text-neutral-900 dark:text-white mt-0.5">Transaksional Aman</div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Simulated Live WhatsApp Transactional Chat UI --}}
                <div class="lg:col-span-6">
                    <div
                        class="relative rounded-2xl bg-neutral-900 p-3 sm:p-4 shadow-2xl border border-neutral-800 ring-1 ring-neutral-700/50">

                        {{-- Chat Window Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-800 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-xs">
                                    <i data-lucide="store" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-neutral-200">Kopi Seduh Indonesia (Official)</div>
                                    <div class="text-[10px] text-emerald-400 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Akun Bisnis
                                        Terverifikasi
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-neutral-500 font-mono">Enkripsi End-to-End</span>
                        </div>

                        {{-- WhatsApp Bubble Simulation Area --}}
                        <div class="py-4 space-y-3 bg-[#0B141A] rounded-xl p-3 my-2 border border-neutral-800/80 text-left">

                            {{-- Incoming Trigger Bubble --}}
                            <div
                                class="max-w-[85%] bg-[#005C4B] text-white p-3 rounded-2xl rounded-tl-none shadow-sm space-y-2 text-xs">
                                <div class="text-[11px] leading-relaxed">
                                    Halo <strong>Kak Nadia Saraswati</strong>! ✨ Terima kasih telah berbelanja di
                                    <strong>Kopi Seduh — Outlet Sudirman</strong>.
                                </div>

                                {{-- Receipt Summary Mini Box --}}
                                <div
                                    class="p-2 rounded-xl bg-black/30 border border-white/10 font-mono text-[10px] space-y-1">
                                    <div class="flex justify-between text-neutral-300">
                                        <span>No. Nota:</span>
                                        <span class="text-white font-semibold">#TRX-9402</span>
                                    </div>
                                    <div class="flex justify-between text-neutral-300">
                                        <span>Total Belanja:</span>
                                        <span class="text-emerald-300 font-semibold">Rp 79.200 (Lunas QRIS)</span>
                                    </div>
                                    <div class="flex justify-between text-neutral-300">
                                        <span>Poin Diperoleh:</span>
                                        <span class="text-amber-300">+10 Poin (Total: 845 Pts)</span>
                                    </div>
                                </div>

                                <div class="text-[11px] leading-relaxed">
                                    Struk digital lengkap dapat dilihat melalui tautan resmi berikut:
                                </div>

                                <div class="pt-1">
                                    <span
                                        class="inline-block px-3 py-1.5 rounded-lg bg-emerald-800/80 hover:bg-emerald-700 text-white font-mono text-[10px] border border-emerald-600/40">
                                        https://cooca.link/receipt/9402
                                    </span>
                                </div>

                                <div
                                    class="text-right text-[9px] text-emerald-200/60 pt-0.5 flex items-center justify-end gap-1">
                                    <span>14:22</span>
                                    <i data-lucide="check-check" class="w-3 h-3 text-sky-400"></i>
                                </div>
                            </div>

                            {{-- Second Notification Mockup: Order Status --}}
                            <div
                                class="max-w-[85%] bg-[#005C4B] text-white p-3 rounded-2xl rounded-tl-none shadow-sm space-y-1.5 text-xs">
                                <div class="text-[11px] leading-relaxed">
                                    🔔 <strong>Update Pesanan:</strong> Kopi Susu Aren dan Croissant Butter Anda sedang
                                    disiapkan oleh Barista. Silakan ambil di konter saat nomor antrean <strong>#04</strong>
                                    dipanggil.
                                </div>
                                <div
                                    class="text-right text-[9px] text-emerald-200/60 pt-0.5 flex items-center justify-end gap-1">
                                    <span>14:23</span>
                                    <i data-lucide="check-check" class="w-3 h-3 text-sky-400"></i>
                                </div>
                            </div>

                        </div>

                        {{-- Bottom Automated Triggers Strip --}}
                        <div
                            class="pt-2 border-t border-neutral-800 flex items-center justify-between text-[11px] text-neutral-400">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="zap" class="w-3.5 h-3.5 text-emerald-400"></i>
                                Otomasi: Terkirim instan dari tombol kasir
                            </span>
                            <a href="{{ route('public.demo') }}" class="text-emerald-400 hover:underline font-medium">Lihat
                                Format Pesan →</a>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        {{-- 2. PAIN POINTS: Masalah Komunikasi Konvensional --}}
        <section
            class="py-16 sm:py-20 bg-neutral-50/70 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/60">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-3">
                        Tantangan Komunikasi Pelanggan</h2>
                    <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        Mengapa Kertas Struk & Chat Manual Sering Merugikan Bisnis Anda?
                    </p>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 mt-3">
                        Biaya kertas struk terus membengkak, struk fisik sering hilang oleh pelanggan saat klaim garansi,
                        dan kasir Anda kewalahan mengetik nomor HP pelanggan satu per satu.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="scroll" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Pemborosan Biaya Kertas Struk</h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Membeli roll kertas printer thermal jutaan rupiah setiap bulan, padahal sebagian besar struk
                            langsung dibuang oleh pelanggan ke tempat sampah toko 10 detik setelah dicetak.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="phone-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Salah Ketik Nomor & Chat Lambat
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Staf harus menyimpan nomor ke buku telepon ponsel toko, mengetik ulang nomor nota pesanan, dan
                            baru bisa mengirim pesan. Proses manual memakan waktu hingga 3 menit per pelanggan.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="message-square-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Pelanggan Mengeluh Status Order
                        </h3>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Pelanggan bolak-balik bertanya lewat telepon apakah barangnya sudah selesai diperbaiki atau
                            dikirim. Tanpa notifikasi otomatis, staf Anda lelah melayani pertanyaan status yang berulang.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE WHATSAPP CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-3">
                    Kemampuan Notifikasi WhatsApp COOCA</h2>
                <p class="text-3xl sm:text-4xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    Komunikasi Transaksional Otomatis yang Ramah Pelanggan
                </p>
                <p class="text-neutral-600 dark:text-neutral-400 text-sm sm:text-base mt-3">
                    Membuat pelanggan merasa dihargai dengan informasi transaksi yang cepat, jelas, dan profesional.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Struk Kasir Digital (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Struk Digital Lengkap Seketika
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Begitu kasir menekan tombol selesai di POS, pelanggan menerima link struk digital resmi. Struk
                            menampilkan rincian barang, harga, pajak, metode bayar QRIS, sisa poin reward, serta link Google
                            Maps untuk memberikan ulasan bintang lima ke toko Anda.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between text-xs">
                        <span class="text-neutral-600 dark:text-neutral-300 font-medium">Penghematan Toko:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                            <i data-lucide="leaf" class="w-4 h-4"></i> Hemat hingga 90% Biaya Pembelian Kertas Thermal
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Notifikasi Status Pengerjaan (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <i data-lucide="bell-ring" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                            Update Status Pesanan Real-Time
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            Sempurna untuk laundry, bengkel kendaraan, dan jasa perbaikan. Saat teknisi mengubah status
                            order menjadi 'Selesai', sistem otomatis mengirim pesan kepada pelanggan bahwa barang siap
                            diambil.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 text-xs flex items-center justify-between font-mono">
                        <span class="text-neutral-500">Pemicu Pesan</span>
                        <span class="text-teal-500 font-bold">Status: Siap Diambil</span>
                    </div>
                </div>

                {{-- Bento Card 3: Pengingat Invoice Tempo / AR (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Reminder Tagihan Sopan</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Kirimkan pengingat invoice jatuh tempo dengan kata-kata yang santun lengkap dengan detail tagihan
                        dan link pembayaran online agar piutang bisnis cepat cair.
                    </p>
                </div>

                {{-- Bento Card 4: Template Teks Kustom (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Template Pesan Fleksibel</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Sesuaikan gaya sapaan dan nada bahasa sesuai kepribadian bisnis Anda, dari bahasa formal untuk
                        korporat hingga sapaan hangat untuk kafe kekinian.
                    </p>
                </div>

                {{-- Bento Card 5: Keamanan & Privasi Nomor (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-neutral-50 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800 space-y-4">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Privasi Terjaga Penuh</h3>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        Nomor WhatsApp pelanggan Anda tersimpan terenkripsi di server pusat. Mencegah penyalahgunaan data
                        kontak oleh staf untuk kepentingan pribadi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Bagaimana Notifikasi WhatsApp Mengalir --}}
        <section class="py-16 sm:py-20 bg-neutral-900 text-white relative overflow-hidden">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-semibold mb-3 border border-emerald-500/30">
                        <span>Alur Komunikasi Otomatis</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Dari Peristiwa Operasional Menjadi Pesan Ramah
                    </h2>
                    <p class="text-neutral-400 text-sm sm:text-base mt-3">
                        Setiap interaksi bisnis disampaikan ke pelanggan secara instan tanpa membebani kasir Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/40">
                            1</div>
                        <h3 class="text-base font-bold text-white">Kasir Selesaikan Order</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Kasir menuntaskan transaksi di layar tablet atau komputer toko dan menanyakan nomor WhatsApp
                            pelanggan untuk pengiriman nota transaksi digital.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-teal-600/30 text-teal-400 flex items-center justify-center font-bold text-xs border border-teal-500/40">
                            2</div>
                        <h3 class="text-base font-bold text-white">Tautan Digital Terbit</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Sistem menghasilkan halaman web struk terenkripsi dengan ringkasan pembelian, PPN, dan rincian
                            poin loyalitas yang langsung siap dibaca.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-600/30 text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-500/40">
                            3</div>
                        <h3 class="text-base font-bold text-white">Pesan Tiba di WhatsApp</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Pesan notifikasi tiba di smartphone pelanggan dalam 3 detik. Pelanggan dapat menyimpan struk
                            tersebut di histori chat mereka kapan pun dibutuhkan.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-neutral-950/80 border border-neutral-800 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-600/30 text-purple-400 flex items-center justify-center font-bold text-xs border border-purple-500/40">
                            4</div>
                        <h3 class="text-base font-bold text-white">Peningkatan Kepuasan</h3>
                        <p class="text-xs text-neutral-400 leading-relaxed">
                            Pelanggan merasa layanan toko Anda modern dan higienis, serta termotivasi mengumpulkan poin
                            untuk datang kembali di kunjungan berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-2">
                    Pertanyaan Umum</h2>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">Tanya Jawab Seputar Notifikasi
                    WhatsApp</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bagaimana cara kerja pengiriman struk kasir lewat WhatsApp di COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Saat transaksi selesai di layar kasir POS, kasir cukup memasukkan nomor WhatsApp pelanggan dan
                        menekan tombol 'Kirim Nota WA'. Sistem menyiapkan pesan teks sopan dengan tautan struk digital resmi
                        yang langsung dapat dibuka oleh pelanggan tanpa aplikasi tambahan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah staf toko harus menyimpan nomor telepon pelanggan ke kontak HP mereka?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Sama sekali tidak. Pengiriman notifikasi dilakukan langsung melalui sistem COOCA menggunakan nomor
                        resmi bisnis Anda. Nomor kontak pelanggan tersimpan aman di database perusahaan dan tidak bocor ke
                        perangkat pribadi staf.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Apakah pesan WhatsApp ini aman dari pemblokiran (banned)?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        COOCA mengutamakan komunikasi transaksional nyata yang memang diharapkan oleh pelanggan—seperti
                        konfirmasi struk pembelian, status cucian selesai, atau nota servis kendaraan. Kami tidak
                        memfasilitasi pesan spam massal tanpa izin sehingga reputasi nomor bisnis Anda tetap terjaga sehat.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/80 dark:border-neutral-800 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-neutral-900 dark:text-white">
                        <span>Bisakah template pesan teks disesuaikan dengan gaya bahasa brand kami?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-neutral-500 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur template sapaan, nama toko, ucapan terima kasih, dan menyisipkan variabel
                        dinamis seperti nama pelanggan, nomor nota, total belanja, serta saldo poin loyalitas.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-neutral-200/70 dark:border-neutral-800">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2
                        class="text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-semibold mb-1">
                        Modul Terkait</h2>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Ekosistem Komunikasi
                        Pelanggan</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                        Point of Sale (POS)</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Layar kasir cepat dengan tombol
                        pengiriman struk digital via WhatsApp.</p>
                </a>

                <a href="{{ route('public.erp.crm') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                        CRM & Database Pelanggan</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Simpan nomor kontak pelanggan secara
                        terpusat dan aman di cloud.</p>
                </a>

                <a href="{{ route('public.omnichannel.orders') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                        Manajemen Pesanan</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Perbarui status order dan kirimkan
                        notifikasi otomatis ke pembeli.</p>
                </a>

                <a href="{{ route('public.erp.finance') }}"
                    class="p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200/70 dark:border-neutral-800 hover:border-emerald-500/50 transition-all group">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-neutral-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                        Kontrol Piutang (AR)</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Kirimkan link tagihan invoice jatuh
                        tempo langsung ke WhatsApp klien.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-950 to-neutral-900 border border-neutral-800 p-8 sm:p-12 text-center text-white relative overflow-hidden">
                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Mulai Komunikasi Transaksional Modern dengan Pelanggan Anda
                    </h2>
                    <p class="text-sm sm:text-base text-neutral-400">
                        Tingkatkan efisiensi kasir, hemat jutaan rupiah biaya kertas struk, dan beri pengalaman terbaik
                        untuk konsumen Anda dengan integrasi WhatsApp COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition-all shadow-md">
                            Coba Demo Notifikasi WhatsApp
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 font-semibold text-sm border border-neutral-700 transition-all">
                            Konsultasi Kebutuhan Toko
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
