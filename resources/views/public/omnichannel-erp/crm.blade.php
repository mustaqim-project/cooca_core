@extends('layouts.public_marketing')

@section('title', 'Software CRM & Sistem Loyalitas Pelanggan Terintegrasi POS | COOCA')
@section('description',
    'Aplikasi CRM dan database pelanggan multi-cabang untuk bisnis retail & jasa. Bangun program membership poin, lacak riwayat belanja omnichannel, dan segmentasi otomatis untuk meningkatkan repeat order.')
@section('og_title', 'Software CRM & Sistem Loyalitas Pelanggan Terintegrasi POS | COOCA')
@section('og_description',
    'Aplikasi CRM dan database pelanggan multi-cabang untuk bisnis retail & jasa. Bangun program membership poin, lacak riwayat belanja omnichannel, dan segmentasi otomatis untuk meningkatkan repeat order.')
@section('keywords',
    'software crm pelanggan, sistem membership loyalitas poin, database pelanggan retail, aplikasi retensi pelanggan, crm terintegrasi pos')

    @push('seo')
        <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "COOCA CRM & Customer Loyalty",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web, Android, iOS, Windows, macOS",
  "description": "Platform database pelanggan 360 derajat terintegrasi kasir POS dan channel online untuk mendorong retensi dan repeat order pelanggan.",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "featureList": [
    "Database pelanggan terpusat dari kasir outlet fisik, website, dan marketplace",
    "Riwayat transaksi lengkap, produk favorit, dan total belanja (Customer Lifetime Value)",
    "Program membership berjenjang (Tier Silver, Gold, Platinum) dan reward poin",
    "Segmentasi otomatis berbasis aktivitas belanja (Pelanggan Aktif vs At-Risk)",
    "Integrasi pengiriman pesan promo personal langsung ke WhatsApp pelanggan"
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
      "name": "Bagaimana kasir toko mendaftarkan pelanggan baru saat transaksi sedang ramai?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat cepat. Kasir cukup meminta nomor WhatsApp dan nama panggilan pelanggan di layar POS dalam waktu 5 detik. Profil pelanggan langsung aktif, poin transaksi pertama langsung masuk, dan pelanggan dapat menerima nota digital via WhatsApp."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah poin belanja bisa digunakan di cabang outlet yang berbeda?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya, poin berlaku universal di seluruh cabang yang terhubung dengan akun COOCA Anda. Pelanggan yang berbelanja di Cabang A dapat menukarkan poin diskonnya saat bertransaksi di Cabang B."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah data kontak pelanggan saya aman jika ada staf atau kasir yang mengundurkan diri?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sangat aman. Database pelanggan tersimpan di cloud terpusat milik perusahaan. Staf kasir hanya bisa melihat nama dan sisa poin saat melayani pembayaran, dan hak akses untuk mengekspor database nomor telepon hanya dimiliki oleh Owner atau Admin yang diberi izin khusus."
      }
    },
    {
      "@type": "Question",
      "name": "Bisakah kami mengimpor database kontak pelanggan lama dari file Excel?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tentu bisa. COOCA menyediakan template impor Excel untuk memasukkan daftar nama, nomor telepon, alamat, dan saldo poin awal pelanggan lama Anda secara massal dalam hitungan menit."
      }
    }
  ]
}
</script>
    @endpush

@section('content')
    <div
        class="relative overflow-hidden bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <!-- ═══ 1. HERO SECTION (Full Viewport 50/50 Ratio - Apple HIG Cockpit) ══════ -->
        <!-- ══════════════════════════════════════════════════════════════════════════ -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">

            <!-- Subtle Ambient Background Glows (Pure CSS, No Heavy Images) -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Safe from Fixed Bottom Nav on Mobile) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-3 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-6 sm:pb-24 lg:py-14">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-6 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile and Desktop ~ 6 Cols) -->
                    <div
                        class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Breadcrumb & Overline Kicker -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                    Customer 360° &amp; Loyalty Points Engine
                                </p>
                            </div>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                                Ubah Pembeli Sekali Datang Menjadi <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">Pelanggan
                                    Setia yang Terus Kembali</span>
                            </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            Biaya mencari pelanggan baru jauh lebih mahal daripada mempertahankan pelanggan lama. COOCA CRM menyatukan riwayat belanja pelanggan dari kasir toko fisik dan channel online, mengelola poin reward, serta memicu repeat order secara teratur.
                        </p>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row items-center justify-start gap-2 sm:gap-3.5 w-full sm:w-auto">
                            <a href="{{ route('public.demo') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0 border border-white/20">
                                <span>Coba Modul CRM Sekarang</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            </a>
                            <a href="{{ route('public.erp.pos') }}"
                                class="h-10 sm:h-12 px-4 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 backdrop-blur-sm active:scale-[0.98] transition-all min-h-[40px] sm:min-h-[48px] shrink-0">
                                <span>Lihat Integrasi Kasir POS</span>
                            </a>
                        </div>

                        <!-- Social Proof & Customer Rating (High Trust Proof) -->
                        <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                            <div class="flex -space-x-2 overflow-hidden shrink-0">
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>NS</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>VIP</span>
                                </div>
                                <div
                                    class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                    <span>RFM</span>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center">
                                <div class="flex items-center gap-1 text-amber-400">
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                    <span class="text-xs sm:text-sm font-extrabold text-white ml-1 tabular-nums">4.9 /
                                        5.0</span>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] text-slate-400 font-medium">Retensi Pelanggan &amp; WhatsApp Broadcast</span>
                            </div>
                        </div>

                        <!-- Reassurance Checkpoints (Left-Aligned on Mobile & Desktop) -->
                        <div
                            class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-3 sm:gap-x-5 gap-y-1 text-[10px] sm:text-xs text-slate-300">
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Nomor WhatsApp ID</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Poin &amp; Tier Member</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-1.5">
                                <i data-lucide="check" class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-400"></i>
                                <span>Segmentasi RFM Otomatis</span>
                            </div>
                        </div>

                    </div>

                    <!-- KANAN: Simulated Live Customer 360° Profile UI -->
                    <div class="lg:col-span-6 relative w-full max-w-xl mx-auto lg:max-w-none">
                        <!-- Ambient Spotlight Glow behind the Terminal Window -->
                        <div
                            class="absolute -inset-2 sm:-inset-4 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C4D8]/15 to-transparent rounded-[32px] sm:rounded-[36px] blur-2xl sm:blur-3xl pointer-events-none -z-10">
                        </div>

                        <!-- Mobile Live Dynamic Island Metric Strip (Clean, non-colliding, zero overlap on Mobile) -->
                        <div class="flex sm:hidden items-center justify-between gap-2 mb-2 w-full">
                            <!-- Mobile Left Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="star" class="w-3 h-3 text-amber-400 fill-amber-400"></i>
                                <span class="text-slate-400 font-medium">Tier</span>
                                <span class="font-extrabold text-amber-400">VIP Gold</span>
                            </div>

                            <!-- Mobile Right Live Badge -->
                            <div
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#0A122C]/95 border border-white/20 text-[10px] text-slate-200 backdrop-blur-xl shadow-md">
                                <i data-lucide="sparkles" class="w-3 h-3 text-[#00C4D8]"></i>
                                <span class="text-slate-400 font-medium">Saldo</span>
                                <span class="font-extrabold text-white">845 Pts</span>
                            </div>
                        </div>

                        <!-- Floating Card Top-Right: Customer LTV (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -top-5 -right-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,122,255,0.2)] min-w-[170px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[11px] text-slate-400 font-medium">Customer LTV</div>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">Rp 8.450.000</div>
                            <div class="text-[11px] font-semibold text-amber-400 flex items-center gap-1 mt-0.5">
                                <i data-lucide="award" class="w-3 h-3"></i>
                                <span>18x Belanja &bull; VIP Gold</span>
                            </div>
                        </div>

                        <!-- Floating Card Bottom-Left: WA Automation (TABLET & DESKTOP - Zero mobile overlap) -->
                        <div
                            class="hidden sm:block absolute -bottom-5 -left-3 z-30 bg-[#0A122C]/95 border border-white/20 rounded-[18px] p-3.5 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.8),0_0_20px_rgba(0,196,216,0.18)] min-w-[160px] backdrop-blur-2xl text-white transform hover:-translate-y-0.5 transition-all">
                            <div class="text-[11px] text-slate-400 font-medium">Automated Trigger</div>
                            <div class="text-lg font-extrabold text-white tabular-nums tracking-tight mt-0.5">WhatsApp Bot</div>
                            <div class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                <span>Voucher Ultah Terkirim</span>
                            </div>
                        </div>

                        <!-- Main Chassis with Specular Top Highlight -->
                        <div
                            class="rounded-[18px] sm:rounded-[28px] bg-[#0A122C]/90 border border-white/15 p-2.5 sm:p-4 shadow-[0_30px_90px_-20px_rgba(0,0,0,0.85),0_0_60px_rgba(0,122,255,0.12)] backdrop-blur-2xl space-y-2.5 sm:space-y-3 text-white relative z-10 overflow-hidden mb-6 sm:mb-0">

                            <!-- Top Edge Specular Glare -->
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none">
                            </div>

                            <!-- Mobile Window Header (sm:hidden - Clean title & status, zero truncation) -->
                            <div class="flex sm:hidden items-center justify-between border-b border-white/10 pb-2 gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                    <span class="text-[11px] font-bold text-white tracking-tight truncate">Customer 360 &bull; Nadia Saraswati</span>
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-400 text-[10px] font-semibold shrink-0">
                                    VIP Gold
                                </span>
                            </div>

                            <!-- Tablet & Desktop macOS Window Title Bar (hidden sm:flex) -->
                            <div class="hidden sm:flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#27C93F]/80"></span>
                                    <div
                                        class="ml-2 flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-[11px] text-slate-300 font-mono">
                                        <i data-lucide="lock" class="w-2.5 h-2.5 text-emerald-400"></i>
                                        <span>cooca.id/app/crm/customer-360</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span class="text-[11px] font-medium text-amber-400 font-mono">VIP Gold Member</span>
                                </div>
                            </div>

                            {{-- Profile Header Card --}}
                            <div
                                class="p-2.5 sm:p-3.5 rounded-xl bg-[#060B1E]/80 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div
                                        class="w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#00C4D8] flex items-center justify-center text-white font-bold text-xs sm:text-sm shadow-md shrink-0">
                                        NS
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-white text-xs sm:text-sm truncate">Nadia Saraswati</h3>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/40 shrink-0">VIP Gold</span>
                                        </div>
                                        <div class="text-[10px] sm:text-[11px] text-slate-400 font-mono mt-0.5 truncate">+62 812-9844-xxxx • ID: CUST-8821</div>
                                    </div>
                                </div>
                                <div class="sm:text-right shrink-0">
                                    <div class="text-[10px] text-slate-400">Saldo Poin</div>
                                    <div class="text-sm sm:text-base font-bold text-[#00C4D8] font-mono">845 Pts</div>
                                </div>
                            </div>

                            {{-- Customer Lifetime Stats Grid --}}
                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2 my-1.5 sm:my-2 text-center">
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 min-w-0">
                                    <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">Total Belanja (LTV)</div>
                                    <div class="text-[11px] sm:text-xs font-bold text-white font-mono mt-0.5 truncate">Rp 8.450.000</div>
                                </div>
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 min-w-0">
                                    <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">Frekuensi Belanja</div>
                                    <div class="text-[11px] sm:text-xs font-bold text-white font-mono mt-0.5 truncate">18 Transaksi</div>
                                </div>
                                <div class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 min-w-0">
                                    <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">Rata-rata Basket</div>
                                    <div class="text-[11px] sm:text-xs font-bold text-white font-mono mt-0.5 truncate">Rp 469.000</div>
                                </div>
                            </div>

                            {{-- Recent Omnichannel Purchase Timeline --}}
                            <div class="space-y-1.5 text-xs">
                                <div class="text-[10px] uppercase font-mono text-slate-400 px-1">Riwayat Transaksi Lintas Channel:</div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <span class="p-1 rounded bg-[#007AFF]/20 text-[#00C4D8] shrink-0">
                                            <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-white text-[11px] truncate">POS Outlet Sudirman</div>
                                            <div class="text-[10px] text-slate-400 truncate">2x Croissant Butter, 2x Kopi Susu Aren</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-white font-mono text-[11px] whitespace-nowrap">Rp 100.000</div>
                                        <div class="text-[10px] text-[#00C4D8] whitespace-nowrap">+10 Poin</div>
                                    </div>
                                </div>

                                <div
                                    class="p-2 sm:p-2.5 rounded-xl bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <span class="p-1 rounded bg-emerald-500/20 text-emerald-400 shrink-0">
                                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-white text-[11px] truncate">Website Online Store</div>
                                            <div class="text-[10px] text-slate-400 truncate">1x Kopi Biji Arabika 1kg (Kirim ke Rumah)</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-white font-mono text-[11px] whitespace-nowrap">Rp 280.000</div>
                                        <div class="text-[10px] text-[#00C4D8] whitespace-nowrap">+28 Poin</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Retention Action Trigger --}}
                            <div
                                class="mt-2 p-2.5 rounded-xl bg-[#007AFF]/15 border border-[#007AFF]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="text-white font-medium">Ulang Tahun Minggu Depan:</span>
                                        <span class="text-slate-300 text-[11px]"> Kirim reward voucher personal</span>
                                    </div>
                                </div>
                                <button
                                    class="px-3 py-1 rounded-lg bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-[10px] flex items-center gap-1 transition-colors shrink-0 self-end sm:self-auto">
                                    <i data-lucide="message-square" class="w-3 h-3"></i>
                                    <span>Kirim WA</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PAIN POINTS: Mengapa Bisnis Kehilangan Pelanggan Terbaiknya --}}
        <section class="py-16 sm:py-20 bg-[#FAFAFC] dark:bg-[#070A14] border-y border-slate-200/80 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                        Tantangan Retensi Konsumen
                    </h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Apakah Anda Tahu Siapa 20% Pelanggan yang Menyumbang 80% Omzet Toko Anda?
                    </p>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3">
                        Kebanyakan bisnis melayani ratusan orang setiap hari, namun tidak pernah mencatat siapa mereka
                        sehingga tidak bisa menghubungi mereka kembali saat toko sedang sepi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Pain 1 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                            <i data-lucide="user-x" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Pelanggan Setia Hilang Tanpa
                            Disadari</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Seorang pelanggan yang biasanya datang seminggu dua kali tiba-tiba tidak pernah muncul lagi
                            selama 2 bulan. Tanpa sistem CRM, Anda baru menyadarinya saat mereka sudah beralih ke
                            kompetitor.
                        </p>
                    </div>

                    {{-- Pain 2 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="database-zap" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Data Kontak Dibawa Kabur Staf</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Nomor kontak klien hanya tersimpan di WhatsApp pribadi karyawan penjualan atau kasir. Begitu
                            staf tersebut resign, seluruh hubungan bisnis dengan pelanggan tersebut terputus total.
                        </p>
                    </div>

                    {{-- Pain 3 --}}
                    <div
                        class="p-6 rounded-2xl bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 shadow-sm space-y-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900/50 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="megaphone-off" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Broadcast Promo Sembarangan (Spam)
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Mengirim pesan promo yang sama ke semua orang tanpa segmentasi. Pelanggan merasa terganggu
                            karena penawaran tidak relevan, hingga akhirnya memblokir nomor WhatsApp bisnis Anda.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. CORE CRM FEATURES: Bento Apple HIG --}}
        <section class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-20 bg-white dark:bg-[#0B132B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs uppercase tracking-widest text-[#007AFF] dark:text-[#00C4D8] font-bold mb-3">
                    Fitur CRM & Retensi Pelanggan
                </h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Membangun Komunitas Pelanggan yang Terus Bertransaksi
                </p>
                <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3">
                    Dirancang untuk memudahkan bisnis retail, cafe, klinik, dan bengkel mengelola hubungan personal dalam
                    skala besar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                {{-- Bento Card 1: 360 Unified Profile (Span 7) --}}
                <div
                    class="md:col-span-7 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-purple-100 dark:bg-purple-950 flex items-center justify-center text-[#007AFF] dark:text-[#00C4D8]">
                            <i data-lucide="contact-2" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Profil Pelanggan 360° yang Lengkap
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Ketahui siapa pelanggan Anda secara mendalam: riwayat belanja offline dan online, produk yang
                            paling sering dibeli, tanggal ulang tahun, catatan alergi/preferensi khusus, serta total nominal
                            belanja seumur hidup (Customer Lifetime Value).
                        </p>
                    </div>

                    <div
                        class="mt-6 p-4 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Pengenal Universal:</span>
                        <span class="text-[#007AFF] dark:text-[#00C4D8] font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4"></i> Satu Nomor HP untuk Seluruh Cabang & Online
                        </span>
                    </div>
                </div>

                {{-- Bento Card 2: Loyalty Points & Tier Member (Span 5) --}}
                <div
                    class="md:col-span-5 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div
                            class="w-11 h-11 rounded-2xl bg-pink-100 dark:bg-pink-950 flex items-center justify-center text-pink-600 dark:text-pink-400">
                            <i data-lucide="gift" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Program Poin & Membership Tier
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Berikan apresiasi nyata kepada pelanggan. Atur aturan perolehan poin per kelipatan belanja dan
                            buat tingkatan member (Silver, Gold, Platinum) dengan benefit diskon khusus yang memotivasi
                            mereka untuk berbelanja lebih banyak.
                        </p>
                    </div>

                    <div
                        class="mt-6 p-3 rounded-2xl bg-white dark:bg-[#060B1E]/60 border border-slate-200/80 dark:border-white/10 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Tier Naik Otomatis</span>
                        <span class="text-pink-600 dark:text-pink-400 font-bold">Belanja &gt; Rp 5 Juta → Gold</span>
                    </div>
                </div>

                {{-- Bento Card 3: Segmentasi Cerdas RFM (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="pie-chart" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Segmentasi Otomatis (RFM)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Sistem mengelompokkan pelanggan secara pintar: Pelanggan Setia (Loyal), Pelanggan Baru, Pelanggan
                        Nilai Tinggi (Big Spenders), hingga Pelanggan Pasif (At-Risk) yang sudah lama tidak berkunjung.
                    </p>
                </div>

                {{-- Bento Card 4: Pemicu Pesan Otomatis (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pemicu Promo Otomatis</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Kirim pesan otomatis saat ulang tahun pelanggan, ucapan terima kasih setelah pembelian pertama, atau
                        voucher pengingat bagi pelanggan yang belum berkunjung lebih dari 30 hari.
                    </p>
                </div>

                {{-- Bento Card 5: Keamanan & Hak Akses Data (Span 4) --}}
                <div
                    class="md:col-span-4 p-6 sm:p-8 rounded-3xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 space-y-4 shadow-sm">
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Keamanan Database Aset</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Data nomor HP dan email pelanggan terlindungi. Hanya manajemen pusat yang memiliki otorisasi untuk
                        mengekspor database, mencegah kebocoran kontak bisnis ke pihak luar.
                    </p>
                </div>

            </div>
        </section>

        {{-- 4. CONNECTED CHAIN: Siklus Retensi Pelanggan (Dark Accent Section) --}}
        <section class="py-16 sm:py-20 bg-[#060B1E] text-white relative overflow-hidden border-y border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 text-[#00C4D8] text-xs font-semibold mb-3 border border-[#00C4D8]/30">
                        <span>Siklus Hubungan Pelanggan Berkelanjutan</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                        Bagaimana COOCA Membantu Bisnis Mempertahankan Pelanggan
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-3">
                        Setiap interaksi pelanggan di kasir maupun online diubah menjadi data yang dapat ditindaklanjuti
                        untuk menghasilkan penjualan berikutnya.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            1
                        </div>
                        <h3 class="text-base font-bold text-white">Input Mudah di Kasir</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Saat bayar, kasir cukup menanyakan nomor WhatsApp. Pelanggan langsung terdaftar tanpa formulir
                            kertas yang merepotkan dan langsung mendapatkan poin belanja.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-pink-500/20 text-pink-400 flex items-center justify-center font-bold text-xs border border-pink-500/30">
                            2
                        </div>
                        <h3 class="text-base font-bold text-white">Riwayat Terkonsolidasi</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Setiap transaksi berikutnya-baik di cabang lain maupun melalui toko online-otomatis menambahkan
                            riwayat belanja ke profil pelanggan yang sama.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center font-bold text-xs border border-[#007AFF]/30">
                            3
                        </div>
                        <h3 class="text-base font-bold text-white">Segmentasi Cerdas</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            COOCA memetakan siapa saja pelanggan yang loyal dan siapa yang sudah lama tidak berbelanja,
                            sehingga promo dapat ditargetkan dengan tepat sasaran.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-500/30">
                            4
                        </div>
                        <h3 class="text-base font-bold text-white">Repeat Order Teratur</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Pelanggan menerima penawaran produk favorit atau pengingat penukaran poin reward sebelum
                            kedaluwarsa, mendorong mereka datang kembali ke toko Anda.
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
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Tanya Jawab Seputar CRM &
                    Database Pelanggan</p>
            </div>

            <div class="space-y-4">
                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bagaimana kasir toko mendaftarkan pelanggan baru saat transaksi sedang ramai?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Sangat cepat. Kasir cukup meminta nomor WhatsApp dan nama panggilan pelanggan di layar POS dalam
                        waktu 5 detik. Profil pelanggan langsung aktif, poin transaksi pertama langsung masuk, dan pelanggan
                        dapat menerima nota digital via WhatsApp.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah poin belanja bisa digunakan di cabang outlet yang berbeda?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Ya, poin berlaku universal di seluruh cabang yang terhubung dengan akun COOCA Anda. Pelanggan yang
                        berbelanja di Cabang A dapat menukarkan poin diskonnya saat bertransaksi di Cabang B.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Apakah data kontak pelanggan saya aman jika ada staf atau kasir yang mengundurkan diri?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Sangat aman. Database pelanggan tersimpan di cloud terpusat milik perusahaan. Staf kasir hanya bisa
                        melihat nama dan sisa poin saat melayani pembayaran, dan hak akses untuk mengekspor database nomor
                        telepon hanya dimiliki oleh Owner atau Admin yang diberi izin khusus.
                    </p>
                </details>

                <details
                    class="group p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 transition-all [&_summary::-webkit-details-marker]:hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                        <span>Bisakah kami mengimpor database kontak pelanggan lama dari file Excel?</span>
                        <i data-lucide="chevron-down"
                            class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Tentu bisa. COOCA menyediakan template impor Excel untuk memasukkan daftar nama, nomor telepon,
                        alamat, dan saldo poin awal pelanggan lama Anda secara massal dalam hitungan menit.
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
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Ekosistem Pengalaman
                        Pelanggan</p>
                </div>
                <a href="{{ route('public.erp.erp') }}"
                    class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#00C4D8] hover:underline mt-2 sm:mt-0 flex items-center gap-1">
                    <span>Lihat Seluruh Modul ERP</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
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
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Identifikasi member dan potong poin diskon
                        langsung di meja kasir.</p>
                </a>

                <a href="{{ route('public.omnichannel.customer') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="users-round" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Omnichannel Customer
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Konsolidasi identitas pembeli dari
                        marketplace dan WhatsApp.</p>
                </a>

                <a href="{{ route('public.content.creation') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-pink-100 dark:bg-pink-950 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Content & Promo Broadcast
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Buat materi promosi menarik untuk disebarkan
                        ke segmen member.</p>
                </a>

                <a href="{{ route('public.erp.analytics') }}"
                    class="p-5 rounded-2xl bg-[#FAFAFC] dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 hover:border-[#007AFF]/50 transition-all group shadow-sm">
                    <div
                        class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors">
                        Analitik Retensi Pelanggan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pantau rasio repeat order dan nilai Customer
                        Lifetime Value (CLV).</p>
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
                        Bangun Hubungan Kuat dengan Pelanggan Bisnis Anda
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300">
                        Mulai kelola database pelanggan secara terpusat dan raih pertumbuhan omzet berkelanjutan melalui
                        program loyalitas yang efektif.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <a href="{{ route('public.demo') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm transition-all shadow-lg shadow-[#007AFF]/25">
                            Coba Demo Modul CRM
                        </a>
                        <a href="{{ route('public.pricing') }}"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm backdrop-blur-sm transition-all">
                            Konsultasi Program Loyalitas
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
