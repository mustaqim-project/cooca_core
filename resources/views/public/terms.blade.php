@extends('layouts.public_marketing')

@section('title', 'Syarat & Ketentuan Layanan (Terms of Service) | Cooca')
@section('description', 'Syarat dan Ketentuan Layanan resmi penggunaan platform Cooca, integrasi API pihak ketiga (TikTok, Meta), dan tata kelola akun pengguna.')

@section('content')
<main class="min-h-screen pt-28 pb-20 bg-[#F5F5F7] dark:bg-[#0A0A0C] text-black dark:text-white antialiased">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Hero Header Bento --}}
        <div class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Ketentuan Penggunaan Resmi</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-black dark:text-white">
                Syarat &amp; Ketentuan Layanan (Terms of Service)
            </h1>
            <p class="text-[14px] sm:text-[15px] text-black/60 dark:text-white/60 leading-relaxed max-w-2xl">
                Terakhir diperbarui: 18 September 2026. Harap membaca ketentuan ini secara seksama sebelum mengakses atau menggunakan platform dan ekosistem terintegrasi Cooca.
            </p>
        </div>

        {{-- Content Bento Card --}}
        <div class="p-8 sm:p-10 rounded-[28px] bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm space-y-8 text-[14px] sm:text-[14.5px] leading-relaxed text-black/80 dark:text-white/80">

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">01</span>
                    Penerimaan Ketentuan
                </h2>
                <p>
                    Dengan mendaftar, mengakses, atau menggunakan layanan di <a href="https://cooca.id" class="text-[#007AFF] hover:underline font-medium">cooca.id</a> ("Platform"), Anda menyatakan bahwa Anda telah membaca, memahami, dan menyetujui untuk terikat dengan Syarat dan Ketentuan ini. Jika Anda tidak menyetujui salah satu poin dalam dokumen ini, Anda disarankan untuk tidak menggunakan layanan kami.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">02</span>
                    Deskripsi Layanan &amp; Integrasi Pihak Ketiga
                </h2>
                <p>
                    Cooca menyediakan sistem operasi bisnis terpadu untuk pelaku UMKM, mencakup Point of Sale (POS), manajemen inventaris, pembukuan keuangan, dan manajemen pemasaran omnichannel. Cooca memungkinkan pengguna menghubungkan akun platform pihak ketiga seperti TikTok, Instagram, dan Facebook untuk mengelola dan mempublikasikan konten promosi usaha secara terpusat.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">03</span>
                    Kepatuhan Terhadap Kebijakan TikTok &amp; Larangan Konten
                </h2>
                <p>
                    Saat menggunakan fitur integrasi TikTok (termasuk <em>TikTok Login Kit</em> dan <em>TikTok Content Posting API</em>), Pengguna wajib mematuhi:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-black/70 dark:text-white/70">
                    <li><a href="https://www.tiktok.com/community-guidelines" target="_blank" class="text-[#007AFF] hover:underline font-medium">Pedoman Komunitas TikTok (TikTok Community Guidelines)</a>.</li>
                    <li><a href="https://www.tiktok.com/legal/terms-of-service" target="_blank" class="text-[#007AFF] hover:underline font-medium">Syarat Layanan TikTok (TikTok Terms of Service)</a>.</li>
                    <li>Peraturan perundang-undangan Republik Indonesia terkait hak cipta, perlindungan konsumen, dan ITE.</li>
                </ul>
                <p>
                    Pengguna dilarang keras mengunggah atau mempublikasikan konten yang mengandung ujaran kebencian, pornografi, aktivitas ilegal, penipuan, pelanggaran hak kekayaan intelektual orang lain, spam berulang, atau malware berbahaya. Cooca berhak memutus koneksi integrasi atau membekukan akun yang terbukti melanggar aturan ini.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">04</span>
                    Kepemilikan Hak Cipta &amp; Konten
                </h2>
                <p>
                    Seluruh konten foto, video, teks, dan deskripsi produk yang diunggah oleh Pengguna tetap merupakan hak milik eksklusif Pengguna. Dengan mengunggah materi tersebut melalui fitur integrasi media sosial Cooca, Anda memberi kami lisensi teknis terbatas yang hanya bertujuan untuk memproses, mengonversi format (bila diperlukan), dan meneruskan konten tersebut ke API platform tujuan atas perintah Anda.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">05</span>
                    Batasan Tanggung Jawab
                </h2>
                <p>
                    Cooca berupaya sebaik mungkin menjaga ketersediaan layanan dan kelancaran komunikasi API pihak ketiga. Namun, Cooca tidak bertanggung jawab atas gangguan layanan, penolakan penayangan konten, atau sanksi akun yang diputuskan secara sepihak oleh TikTok, Meta, atau penyedia pihak ketiga lainnya akibat pelanggaran kebijakan mereka.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-mono">06</span>
                    Kontak &amp; Dukungan Pengguna
                </h2>
                <p>
                    Untuk pengaduan, konsultasi teknis, atau pertanyaan terkait Syarat dan Ketentuan ini, hubungi tim kami:
                </p>
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] space-y-1 font-mono text-[13px]">
                    <div><strong>Layanan Resmi:</strong> PT Cooca Digital Teknologi</div>
                    <div><strong>Email Resmi:</strong> <a href="mailto:support@cooca.id" class="text-[#007AFF]">support@cooca.id</a></div>
                    <div><strong>Website:</strong> <a href="https://cooca.id" class="text-[#007AFF]">https://cooca.id</a></div>
                </div>
            </section>

        </div>

    </div>
</main>
@endsection
