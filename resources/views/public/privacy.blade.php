@extends('layouts.public_marketing')

@section('title', 'Kebijakan Privasi & Perlindungan Data Pengguna | Cooca')
@section('description', 'Kebijakan privasi resmi Cooca mengenai pengumpulan data, enkripsi, integrasi media sosial (TikTok, Meta, Instagram), dan perlindungan hak pengguna.')

@section('content')
<main class="min-h-screen pt-28 pb-20 bg-[#F5F5F7] dark:bg-[#0A0A0C] text-black dark:text-white antialiased">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Hero Header Bento --}}
        <div class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Komitmen Keamanan &amp; Privasi Terbuka</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-black dark:text-white">
                Kebijakan Privasi &amp; Data Pribadi
            </h1>
            <p class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 leading-relaxed max-w-2xl">
                Terakhir diperbarui: 18 September 2026. Dokumen ini menjelaskan bagaimana Cooca mengumpulkan, mengamankan, dan memproses data Anda, termasuk saat menggunakan integrasi platform pihak ketiga seperti TikTok Developer API dan Meta Platform.
            </p>
        </div>

        {{-- Content Bento Card --}}
        <div class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm space-y-8 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">01</span>
                    Pendahuluan &amp; Ruang Lingkup
                </h2>
                <p>
                    Cooca (<a href="https://cooca.id" class="text-[#007AFF] hover:underline font-medium">cooca.id</a>) adalah platform SaaS ERP dan Business Operating System untuk pelaku usaha UMKM di Indonesia. Privasi dan keamanan data bisnis Anda adalah prioritas mutlak kami. Kebijakan ini berlaku untuk seluruh layanan Cooca, termasuk aplikasi web, integrasi API, dan layanan terkait lainnya.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">02</span>
                    Data yang Kami Kumpulkan
                </h2>
                <p>Kami hanya mengumpulkan data yang esensial untuk menjalankan operasional bisnis Anda:</p>
                <ul class="list-disc pl-6 space-y-1.5 text-black/70 dark:text-white/70">
                    <li><strong>Informasi Akun:</strong> Nama pengguna, alamat email, nomor telepon/WhatsApp, dan nama unit bisnis.</li>
                    <li><strong>Data Operasional Bisnis:</strong> Katalog produk, persediaan inventaris, transaksi penjualan POS, dan pembukuan keuangan toko.</li>
                    <li><strong>Integrasi TikTok for Developers:</strong> Jika Anda memilih untuk menghubungkan akun TikTok melalui <em>TikTok Login Kit</em>, kami hanya meminta izin akses dasar (<code class="px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[12px]">user.info.basic</code>) untuk mengambil ID unik kreator (<code class="font-mono text-[12px]">open_id</code>), nama tampilan (display name), dan foto avatar agar Anda dapat mengidentifikasi akun yang terhubung di dashboard Cooca.</li>
                    <li><strong>Izin Penerbitan Konten TikTok:</strong> Lingkup (<code class="px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[12px]">video.upload</code>) digunakan secara eksklusif saat Anda secara sengaja mengunggah media video dari Cooca untuk dipublikasikan atau disimpan sebagai draf di profil TikTok bisnis Anda melalui <em>TikTok Content Posting API</em>.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">03</span>
                    Penggunaan Data &amp; Kebijakan Larangan Penjualan Data
                </h2>
                <p>
                    Data yang dikumpulkan semata-mata digunakan untuk:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-black/70 dark:text-white/70">
                    <li>Memfasilitasi pembuatan, penjadwalan, dan publikasi konten omnichannel (Instagram, Facebook, TikTok).</li>
                    <li>Memproses pesanan, menghitung laporan keuangan otomatis, dan menyinkronkan stok produk.</li>
                    <li>Menyediakan notifikasi status postingan atau peringatan operasional kepada pemilik usaha.</li>
                </ul>
                <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] font-medium text-[13px] flex items-center gap-2.5">
                    <i data-lucide="check-shield" class="w-5 h-5 shrink-0"></i>
                    <span><strong>Jaminan Tegas:</strong> Cooca TIDAK PERNAH dan TIDAK AKAN PERNAH menjual, menyewakan, atau memperdagangkan data pribadi maupun data akun TikTok/Meta pengguna kepada pihak ketiga atau jaringan periklanan manapun.</span>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">04</span>
                    Keamanan Penyimpanan &amp; Enkripsi Simetris
                </h2>
                <p>
                    Seluruh access token OAuth (termasuk TikTok User Access Token dan Meta Long-Lived Token) disimpan secara terenkripsi menggunakan algoritma <strong>AES-256-CBC</strong> di dalam database dengan kunci rahasia aplikasi server. Token tidak pernah diekspos ke publik atau disimpan dalam teks biasa (plaintext).
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">05</span>
                    Pencabutan Akses &amp; Penghapusan Data (Data Deletion)
                </h2>
                <p>
                    Pengguna memiliki hak penuh untuk mencabut otorisasi dan menghapus token integrasi kapan saja:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-black/70 dark:text-white/70">
                    <li><strong>Melalui Cooca:</strong> Masuk ke menu <em>Media Sosial > Akun Terhubung</em>, lalu klik tombol <em>Putuskan Koneksi</em> pada akun TikTok yang diinginkan. Sistem kami akan segera memusnahkan access token dari basis data kami.</li>
                    <li><strong>Melalui TikTok:</strong> Buka aplikasi TikTok Anda > <em>Pengaturan dan Privasi</em> > <em>Keamanan &amp; Izin</em> > <em>Kelola Izin Aplikasi</em> > pilih <em>Cooca</em> > klik <em>Cabut Akses</em>.</li>
                    <li><strong>Permintaan Penghapusan Akun Total:</strong> Anda dapat mengajukan permohonan penghapusan seluruh data bisnis dan akun dengan menghubungi tim kami di <a href="mailto:support@cooca.id" class="text-[#007AFF] hover:underline font-medium">support@cooca.id</a>. Data akan dihapus permanen dalam jangka waktu maksimal 14 hari kerja.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">06</span>
                    Kontak Resmi Tim Kepatuhan
                </h2>
                <p>
                    Jika Anda memiliki pertanyaan seputar Kebijakan Privasi, penggunaan API TikTok, atau tata kelola data di Cooca, hubungi kami melalui:
                </p>
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] space-y-1 font-mono text-[13px]">
                    <div><strong>Layanan Resmi:</strong> PT Cooca Digital Teknologi (Cooca.id)</div>
                    <div><strong>Email:</strong> <a href="mailto:support@cooca.id" class="text-[#007AFF]">support@cooca.id</a> / <a href="mailto:privacy@cooca.id" class="text-[#007AFF]">privacy@cooca.id</a></div>
                    <div><strong>Website:</strong> <a href="https://cooca.id" class="text-[#007AFF]">https://cooca.id</a></div>
                    <div><strong>Lokasi:</strong> Indonesia</div>
                </div>
            </section>

        </div>

    </div>
</main>
@endsection
