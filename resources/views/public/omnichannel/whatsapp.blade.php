@extends('layouts.public_marketing')

@section('title', 'Software Notifikasi WhatsApp Bisnis, Struk Kasir & Invoice Digital | COOCA')
@section('description',
    'Hubungkan sistem operasional bisnis Anda dengan WhatsApp. Kirim struk kasir digital otomatis, notifikasi status pesanan, update pengerjaan servis, dan pengingat invoice langsung ke chat pelanggan.')
@section('og_title', 'Software Notifikasi WhatsApp Bisnis, Struk Kasir & Invoice Digital | COOCA')
@section('og_description',
    'Hubungkan sistem operasional bisnis Anda dengan WhatsApp. Kirim struk kasir digital otomatis, notifikasi status pesanan, update pengerjaan servis, dan pengingat invoice langsung ke chat pelanggan.')
@section('keywords',
    'software notifikasi whatsapp bisnis, kirim struk kasir via wa, invoice digital whatsapp, notifikasi pesanan wa umkm, reminder tagihan whatsapp')

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
        "text": "COOCA mengutamakan komunikasi transaksional nyata yang memang diharapkan oleh pelanggan-seperti konfirmasi struk pembelian, status cucian selesai, atau nota servis kendaraan. Kami tidak memfasilitasi pesan spam massal tanpa izin sehingga reputasi nomor bisnis Anda tetap terjaga sehat."
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
    <div
        class="relative overflow-hidden bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION (Midnight Blue Standard - Type A Full Viewport) --}}
        <section
            class="relative bg-[#060B1E] text-white min-h-[calc(100svh-84px)] lg:flex lg:items-center py-12 lg:py-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Ambient Glows --}}
            <div
                class="absolute -top-32 -right-32 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute -bottom-32 -left-32 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none -z-0">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
                {{-- Breadcrumb --}}
                <nav class="pb-6" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-xs text-slate-400">
                        <li><a href="{{ route('landing') }}" class="hover:text-[#00C4D8] transition-colors">Home</a></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li><span class="text-slate-400">Omnichannel</span></li>
                        <li><i data-lucide="chevron-right" class="w-3 h-3 text-slate-500"></i></li>
                        <li class="text-white font-semibold" aria-current="page">Integrasi Komunikasi WhatsApp</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
                    {{-- Left Column: Copy & Value Proposition (Mobile Center, Desktop Left ~ 5 Cols) --}}
                    <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Transactional WhatsApp &amp; Digital Receipts
                            </p>

                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Hubungkan Operasional Toko Langsung ke <span class="text-[#00C4D8]">WhatsApp Pelanggan</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            Kirimkan struk kasir digital tanpa kertas thermal, beri tahu pelanggan saat pesanan siap
                            diambil,
                            infokan status pengerjaan servis, dan tagih invoice jatuh tempo langsung ke aplikasi chat yang
                            dibuka pelanggan setiap hari.
                        </p>

                        {{-- Action CTAs (Centered on Mobile, Row on Desktop) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3.5 pt-2 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-lg shadow-[#007AFF]/25 transition-all duration-200 min-h-[48px]">
                                <span>Coba Demo Notifikasi WA</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 shrink-0"></i>
                            </a>
                            <a href="{{ route('public.erp.pos') }}"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white backdrop-blur-sm font-semibold text-sm transition-all min-h-[48px]">
                                <span>Koneksi ke Kasir POS</span>
                            </a>
                        </div>

                        {{-- Key Trust Specs (Centered on Mobile) --}}
                        <div class="pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-3 gap-3.5 text-center sm:text-left w-full">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Bentuk Struk</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Link Digital Resmi</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400 font-medium truncate">Pengiriman Pesan</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Tanpa Simpan Nomor</div>
                            </div>
                            <div class="min-w-0 col-span-2 sm:col-span-1">
                                <div class="text-xs text-slate-400 font-medium truncate">Sifat Komunikasi</div>
                                <div class="text-sm font-bold text-white mt-0.5 truncate">Transaksional Aman</div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Simulated Live WhatsApp Transactional Chat UI (7 Cols ~ 58%) --}}
                    <div class="lg:col-span-7">
                        <div
                            class="relative rounded-2xl bg-[#0E1E45]/80 border border-white/10 p-4 sm:p-5 shadow-2xl backdrop-blur-md text-white">

                            {{-- Chat Window Header --}}
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-white/10 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div
                                        class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                        <i data-lucide="store" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white truncate">Kopi Seduh Indonesia (Official)</div>
                                        <div class="text-[10px] text-emerald-400 flex items-center gap-1 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span> Akun
                                            Bisnis Terverifikasi
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono shrink-0 self-end sm:self-auto">Enkripsi
                                    End-to-End</span>
                            </div>

                            {{-- WhatsApp Bubble Simulation Area --}}
                            <div class="py-4 space-y-3 bg-[#0B141A] rounded-xl p-3 my-2 border border-white/10 text-left">

                                {{-- Incoming Trigger Bubble --}}
                                <div
                                    class="max-w-[90%] sm:max-w-[85%] bg-[#005C4B] text-white p-3 rounded-2xl rounded-tl-none shadow-sm space-y-2 text-xs break-words">
                                    <div class="text-[11px] leading-relaxed">
                                        Halo <strong>Kak Nadia Saraswati</strong>! Terima kasih telah berbelanja di
                                        <strong>Kopi Seduh - Outlet Sudirman</strong>.
                                    </div>

                                    {{-- Receipt Summary Mini Box --}}
                                    <div
                                        class="p-2 rounded-xl bg-black/30 border border-white/10 font-mono text-[10px] space-y-1">
                                        <div class="flex justify-between items-center gap-2 text-slate-300">
                                            <span class="truncate">No. Nota:</span>
                                            <span class="text-white font-semibold shrink-0">#TRX-9402</span>
                                        </div>
                                        <div class="flex justify-between items-center gap-2 text-slate-300">
                                            <span class="truncate">Total Belanja:</span>
                                            <span class="text-emerald-300 font-semibold shrink-0 whitespace-nowrap">Rp
                                                79.200 (QRIS)</span>
                                        </div>
                                        <div class="flex justify-between items-center gap-2 text-slate-300">
                                            <span class="truncate">Poin Diperoleh:</span>
                                            <span class="text-amber-300 shrink-0 whitespace-nowrap">+10 Pts (Total:
                                                845)</span>
                                        </div>
                                    </div>

                                    <div class="text-[11px] leading-relaxed">
                                        Struk digital lengkap dapat dilihat melalui tautan resmi berikut:
                                    </div>

                                    <div class="pt-1">
                                        <span
                                            class="inline-block px-3 py-1.5 rounded-lg bg-emerald-800/80 hover:bg-emerald-700 text-white font-mono text-[10px] border border-emerald-600/40 truncate max-w-full">
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
                                    class="max-w-[90%] sm:max-w-[85%] bg-[#005C4B] text-white p-3 rounded-2xl rounded-tl-none shadow-sm space-y-1.5 text-xs break-words">
                                    <div class="text-[11px] leading-relaxed">
                                        <strong>Update Pesanan:</strong> Kopi Susu Aren dan Croissant Butter Anda sedang
                                        disiapkan oleh Barista. Silakan ambil di konter saat nomor antrean
                                        <strong>#04</strong> dipanggil.
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
                                class="pt-2 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-400">
                                <span class="flex items-center gap-1.5 truncate">
                                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#00C4D8] shrink-0"></i>
                                    <span class="truncate">Otomasi: Terkirim instan dari tombol kasir</span>
                                </span>
                                <a href="{{ route('public.demo') }}"
                                    class="text-[#00C4D8] hover:underline font-medium shrink-0">Lihat Format Pesan →</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Masalah Komunikasi Konvensional --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Tantangan Komunikasi Pelanggan
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Mengapa Kertas Struk & Chat Manual Sering Merugikan Bisnis Anda?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Biaya kertas struk terus membengkak, struk fisik sering hilang oleh pelanggan saat klaim garansi,
                        dan kasir Anda kewalahan mengetik nomor HP pelanggan satu per satu.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="scroll" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pemborosan Biaya Kertas Struk</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Membeli roll kertas printer thermal jutaan rupiah setiap bulan, padahal sebagian besar struk
                            langsung dibuang oleh pelanggan ke tempat sampah toko 10 detik setelah dicetak.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="phone-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Salah Ketik Nomor & Chat Lambat</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Staf harus menyimpan nomor ke buku telepon ponsel toko, mengetik ulang nomor nota pesanan, dan
                            baru bisa mengirim pesan. Proses manual memakan waktu hingga 3 menit per pelanggan.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="message-square-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pelanggan Mengeluh Status Order</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pelanggan bolak-balik bertanya lewat telepon apakah barangnya sudah selesai diperbaiki atau
                            dikirim. Tanpa notifikasi otomatis, staf Anda lelah melayani pertanyaan status yang berulang.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE WHATSAPP CAPABILITIES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-white dark:bg-[#0B132B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                    Kemampuan Notifikasi WhatsApp COOCA
                </h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Komunikasi Transaksional Otomatis yang Ramah Pelanggan
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Membuat pelanggan merasa dihargai dengan informasi transaksi yang cepat, jelas, dan profesional.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: Struk Kasir Digital (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Struk Digital Lengkap Seketika
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Begitu kasir menekan tombol selesai di POS, pelanggan menerima link struk digital resmi. Struk
                            menampilkan rincian barang, harga, pajak, metode bayar QRIS, sisa poin reward, serta link Google
                            Maps untuk memberikan ulasan bintang lima ke toko Anda.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Penghematan Toko:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                            <i data-lucide="leaf" class="w-4 h-4"></i> Hemat hingga 90% Biaya Pembelian Kertas Thermal
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Notifikasi Status Pengerjaan (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-teal-100 dark:bg-teal-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="bell-ring" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Update Status Pesanan Real-Time
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Sempurna untuk laundry, bengkel kendaraan, dan jasa perbaikan. Saat teknisi mengubah status
                            order menjadi 'Selesai', sistem otomatis mengirim pesan kepada pelanggan bahwa barang siap
                            diambil.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Pemicu Pesan</span>
                        <span class="text-teal-600 dark:text-teal-400 font-bold">Status: Siap Diambil</span>
                    </div>
                </div>

                {{-- Bento Card 3: Pengingat Invoice Tempo / AR (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Reminder Tagihan Sopan</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Kirimkan pengingat invoice jatuh tempo dengan kata-kata yang santun lengkap dengan detail tagihan
                        dan link pembayaran online agar piutang bisnis cepat cair.
                    </p>
                </div>

                {{-- Bento Card 4: Template Teks Kustom (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Template Pesan Fleksibel</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Sesuaikan gaya sapaan dan nada bahasa sesuai kepribadian bisnis Anda, dari bahasa formal untuk
                        korporat hingga sapaan hangat untuk kafe kekinian.
                    </p>
                </div>

                {{-- Bento Card 5: Keamanan & Privasi Nomor (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Privasi Terjaga Penuh</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Nomor WhatsApp pelanggan Anda tersimpan terenkripsi di server pusat. Mencegah penyalahgunaan data
                        kontak oleh staf untuk kepentingan pribadi.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Bagaimana Notifikasi WhatsApp Mengalir (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Alur Komunikasi Otomatis</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Dari Peristiwa Operasional Menjadi Pesan Ramah
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Setiap interaksi bisnis disampaikan ke pelanggan secara instan tanpa membebani kasir Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Kasir Selesaikan Order</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Kasir menuntaskan transaksi di layar tablet atau komputer toko dan menanyakan nomor WhatsApp
                            pelanggan untuk pengiriman nota transaksi digital.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-300 flex items-center justify-center font-bold text-xs border border-teal-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Tautan Digital Terbit</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Sistem menghasilkan halaman web struk terenkripsi dengan ringkasan pembelian, PPN, dan rincian
                            poin loyalitas yang langsung siap dibaca.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-500/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-blue-500/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Pesan Tiba di WhatsApp</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Pesan notifikasi tiba di smartphone pelanggan dalam 3 detik. Pelanggan dapat menyimpan struk
                            tersebut di histori chat mereka kapan pun dibutuhkan.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Peningkatan Kepuasan</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Pelanggan merasa layanan toko Anda modern dan higienis, serta termotivasi mengumpulkan poin
                            untuk datang kembali di kunjungan berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. FREQUENTLY ASKED QUESTIONS (FAQ) --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 bg-white dark:bg-[#0B132B]">
            <div class="text-center mb-12">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-2">
                    Pertanyaan Umum
                </h2>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar
                    Notifikasi WhatsApp</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana cara kerja pengiriman struk kasir lewat WhatsApp di COOCA?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Saat transaksi selesai di layar kasir POS, kasir cukup memasukkan nomor WhatsApp pelanggan dan
                        menekan tombol 'Kirim Nota WA'. Sistem menyiapkan pesan teks sopan dengan tautan struk digital resmi
                        yang langsung dapat dibuka oleh pelanggan tanpa aplikasi tambahan.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah staf toko harus menyimpan nomor telepon pelanggan ke kontak HP mereka?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Sama sekali tidak. Pengiriman notifikasi dilakukan langsung melalui sistem COOCA menggunakan nomor
                        resmi bisnis Anda. Nomor kontak pelanggan tersimpan aman di database perusahaan dan tidak bocor ke
                        perangkat pribadi staf.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah pesan WhatsApp ini aman dari pemblokiran (banned)?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        COOCA mengutamakan komunikasi transaksional nyata yang memang diharapkan oleh pelanggan-seperti
                        konfirmasi struk pembelian, status cucian selesai, atau nota servis kendaraan. Kami tidak
                        memfasilitasi pesan spam massal tanpa izin sehingga reputasi nomor bisnis Anda tetap terjaga sehat.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bisakah template pesan teks disesuaikan dengan gaya bahasa brand kami?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Bisa. Anda dapat mengatur template sapaan, nama toko, ucapan terima kasih, dan menyisipkan variabel
                        dinamis seperti nama pelanggan, nomor nota, total belanja, serta saldo poin loyalitas.
                    </p>
                </details>
            </div>
        </section>

        {{-- 6. TOPICAL CLUSTER --}}
        <section
            class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200/80 dark:border-white/10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-1">
                        Modul Terkait
                    </h2>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Ekosistem Komunikasi
                        Pelanggan</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <a href="{{ route('public.erp.pos') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Point of Sale (POS)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Layar kasir cepat dengan tombol pengiriman
                        struk digital via WhatsApp.</p>
                </a>

                <a href="{{ route('public.erp.crm') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        CRM & Database Pelanggan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Simpan nomor kontak pelanggan secara
                        terpusat dan aman di cloud.</p>
                </a>

                <a href="{{ route('public.omnichannel.orders') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-teal-100 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Manajemen Pesanan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui status order dan kirimkan
                        notifikasi otomatis ke pembeli.</p>
                </a>

                <a href="{{ route('public.erp.finance') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Kontrol Piutang (AR)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kirimkan link tagihan invoice jatuh tempo
                        langsung ke WhatsApp klien.</p>
                </a>
            </div>
        </section>

        {{-- 7. BOTTOM CONVERSION CTA --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 pb-24">
            <div
                class="relative p-8 sm:p-14 rounded-[28px] bg-[#060B1E] text-white border border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden text-center">
                {{-- Ambient lights inside CTA --}}
                <div
                    class="absolute -top-24 -right-24 w-80 h-80 bg-[#007AFF]/20 rounded-full blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-80 h-80 bg-[#00C4D8]/15 rounded-full blur-[100px] pointer-events-none">
                </div>

                <div class="max-w-2xl mx-auto space-y-5 relative z-10">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Mulai Komunikasi Transaksional Modern dengan Pelanggan Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Tingkatkan efisiensi kasir, hemat jutaan rupiah biaya kertas struk, dan beri pengalaman terbaik
                        untuk konsumen Anda dengan integrasi WhatsApp COOCA.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Notifikasi WhatsApp
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Konsultasi Kebutuhan Toko
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
