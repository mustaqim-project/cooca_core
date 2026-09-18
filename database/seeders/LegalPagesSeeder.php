<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. PRIVACY POLICY
        $privacyGeneral = <<<'HTML'
<section id="general-policy" class="space-y-4">
    <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[13px] font-mono font-bold">01</span>
        Ketentuan Umum & Kerangka Pelindungan Data Pribadi
    </h2>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Selamat datang di <strong>Cooca</strong> (<a href="https://cooca.id" class="text-[#007AFF] hover:underline font-semibold">https://cooca.id</a>), platform Software-as-a-Service (SaaS) ERP dan Business Operating System terpadu untuk pelaku Usaha Mikro, Kecil, dan Menengah (UMKM) di Indonesia yang dikelola secara resmi oleh <strong>PT Cooca Digital Teknologi</strong> ("Cooca", "Kami").
    </p>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Kebijakan Privasi ini disusun sebagai bentuk transparansi dan kepatuhan mutlak Kami terhadap <strong>Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)</strong>, <strong>Peraturan Pemerintah Nomor 71 Tahun 2019 tentang Penyelenggaraan Sistem dan Transaksi Elektronik (PP PSTE)</strong>, serta ketentuan privasi mitra teknologi global (Meta Platform Tech Provider, TikTok Open API, Google Cloud Console, TriPay Payment Gateway, dan Biteship Logistics).
    </p>
    <div class="p-4 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-[13px] leading-relaxed">
        <strong>Peran Ganda Cooca:</strong> Dalam pemrosesan data, Cooca berperan sebagai <em>Pengendali Data Pribadi</em> (Data Controller) atas data akun terdaftar, serta bertindak sebagai <em>Prosesor Data Pribadi</em> (Data Processor) yang memproses data transaksi dan pelanggan atas instruksi dari Mitra Usaha (Owner Toko).
    </div>
    <div class="space-y-2">
        <h3 class="text-[16px] font-bold text-black dark:text-white">Prinsip Pokok Pelindungan Data Kami:</h3>
        <ul class="list-disc pl-6 space-y-1.5 text-black/70 dark:text-white/70 text-[13.5px]">
            <li><strong>Keabsahan & Transparansi:</strong> Data hanya dikumpulkan atas persetujuan sah dari Subjek Data atau pemenuhan kewajiban kontraktual layanan.</li>
            <li><strong>Batasan Tujuan (Purpose Limitation):</strong> Data hanya digunakan untuk tujuan operasional bisnis, pemrosesan transaksi belanja, pengiriman kurir, dan kepatuhan hukum.</li>
            <li><strong>Minimisasi Data:</strong> Kami hanya mengumpulkan atribut data yang relevan dan benar-benar dibutuhkan.</li>
            <li><strong>Integritas & Keamanan Ketat:</strong> Seluruh data disimpan dalam basis data terisolasi (multi-tenant isolation) dengan enkripsi simetris modern <strong>AES-256-CBC</strong> dan transmisi aman berprotokol <strong>TLS 1.3</strong>.</li>
            <li><strong>Larangan Penjualan Data:</strong> Cooca TIDAK PERNAH dan TIDAK AKAN PERNAH menjual, menyewakan, memperdagangkan, atau memonetisasi data pengguna kepada pihak periklanan atau broker data manapun.</li>
        </ul>
    </div>
</section>

<section id="third-party-integrations" class="space-y-4 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
    <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[13px] font-mono font-bold">02</span>
        Integrasi Ekosistem Pihak Ketiga (Model B Terpusat)
    </h2>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Cooca menerapkan arsitektur <em>Model B (Platform Centralized Architecture)</em> untuk memudahkan UMKM menjalankan bisnis tanpa harus mendaftar API pihak ketiga secara rumit. Dalam model ini, data diteruskan ke penyedia resmi berikut secara aman:
    </p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[13px]">
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
            <div class="font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                TriPay Payment Gateway
            </div>
            <p class="text-black/60 dark:text-white/60 leading-relaxed">
                Memproses pembayaran digital (QRIS Dinamis, Virtual Account, E-Wallet). Cooca menerima webhook konfirmasi pembayaran tanpa pernah menyimpan nomor kartu kredit atau PIN perbankan pengguna.
            </p>
        </div>
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
            <div class="font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span>
                Biteship Logistics Aggregator
            </div>
            <p class="text-black/60 dark:text-white/60 leading-relaxed">
                Meneruskan alamat pengirim toko dan alamat penerima pembeli ke mitra kurir (JNE, J&T, SiCepat, Anteraja, GoSend, Grab, dsb.) guna pembuatan nomor resi waybill dan penjemputan paket.
            </p>
        </div>
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
            <div class="font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                Meta WhatsApp Cloud API v25.0
            </div>
            <p class="text-black/60 dark:text-white/60 leading-relaxed">
                Pengiriman pesan transaksional berkecepatan tinggi: kode OTP login, kuitansi digital transaksi kasir POS, dan pengingat billing tagihan resmi dengan enkripsi end-to-end.
            </p>
        </div>
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
            <div class="font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#AF52DE]"></span>
                Meta & TikTok Developer Open API
            </div>
            <p class="text-black/60 dark:text-white/60 leading-relaxed">
                Otomasi penayangan konten promosi bisnis. Kami hanya meminta izin standar (<em>user.info.basic</em>, <em>video.upload</em>) untuk mempublikasikan materi yang Anda setujui.
            </p>
        </div>
    </div>
</section>

<section id="data-subject-rights" class="space-y-4 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
    <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[13px] font-mono font-bold">03</span>
        Hak-Hak Anda Berdasarkan UU PDP No. 27/2022
    </h2>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Sesuai dengan Bab VI UU Pelindungan Data Pribadi, Anda memiliki hak-hak hukum yang diakui sepenuhnya oleh Cooca:
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[13px]">
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
            <strong>1. Hak Mendapatkan Informasi:</strong> Mengetahui kejelasan identitas, dasar hukum, dan tujuan pemrosesan data pribadi Anda.
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
            <strong>2. Hak Memperbaiki & Merubah:</strong> Memperbarui informasi data pribadi Anda yang tidak akurat melalui pengaturan profil.
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
            <strong>3. Hak Akses & Salinan Data:</strong> Meminta salinan data pribadi yang terekam pada sistem Cooca dalam format terstruktur.
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
            <strong>4. Hak Penghapusan (Right to Erasure):</strong> Meminta pemusnahan atau penghapusan permanen akun dan seluruh riwayat data Anda.
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
            <strong>5. Hak Menarik Persetujuan:</strong> Mencabut otorisasi akses integrasi media sosial atau pengiriman pesan notifikasi sewaktu-waktu.
        </div>
        <div class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
            <strong>6. Hak Pengaduan:</strong> Mengajukan komplain ke Petugas Pelindungan Data (DPO) kami di <a href="mailto:dpo@cooca.id" class="text-[#007AFF] font-mono">dpo@cooca.id</a>.
        </div>
    </div>
</section>
HTML;

        $privacyOwner = <<<'HTML'
<div class="space-y-6">
    <div class="p-5 rounded-[20px] bg-gradient-to-br from-[#007AFF]/10 via-[#007AFF]/5 to-transparent border border-[#007AFF]/20 space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF]">
            <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
            <span>Bagian Khusus: Mitra Pemilik Usaha & UMKM (Owner / Merchant)</span>
        </div>
        <p class="text-[13.5px] text-black/75 dark:text-white/75 leading-relaxed">
            Ketentuan berikut secara khusus mengatur data yang dikumpulkan, diproses, dan dilindungi dari Anda selaku pemilik bisnis, manajer toko, staf kasir, dan penanggung jawab operasional unit usaha.
        </p>
    </div>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-bold">A</span>
            Data Profil Usaha & Verifikasi Akun (KYB / Anti-Money Laundering)
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Untuk mengaktifkan fitur pencairan dana (*payment settlement*) dan mematuhi regulasi pencegahan penipuan serta tindak pidana pencucian uang (Anti-Money Laundering / AML) Bank Indonesia, Kami mengumpulkan:
        </p>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li>Nama lengkap pemilik usaha, alamat surel terverifikasi, dan nomor WhatsApp aktif.</li>
            <li>Nama badan usaha/toko, bidang industri (F&B, Retail, Jasa, dsb.), dan alamat fisik operasional toko.</li>
            <li>Nomor Rekening Bank & Nama Pemilik Rekening resmi untuk tujuan pengiriman hasil penjualan (Settlement Payout).</li>
            <li>Identitas legal (Foto KTP/NIK dan NPWP) yang disimpan di peladen berkeamanan tinggi dengan enkripsi ketat hanya untuk proses audit kepatuhan perbankan.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-bold">B</span>
            Pelindungan Rahasia Dagang & Data Keuangan Internal
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Kami memahami bahwa data pembukuan adalah rahasia dapur bisnis yang paling sensitif. Cooca menjamin:
        </p>
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[13px]">
            <p class="text-black/80 dark:text-white/80">
                <strong>Kepemilikan Mutlak:</strong> Seluruh formula Harga Pokok Penjualan (HPP), rincian Bill of Materials (BOM), margin keuntungan kotor/bersih, neraca keuangan, buku besar, dan pergerakan stok inventaris adalah hak milik eksklusif Mitra Usaha. Cooca tidak memiliki hak komersial atas resep, strategi harga, ataupun formula produk Anda.
            </p>
            <p class="text-black/80 dark:text-white/80">
                <strong>Isolasi Multi-Tenant Mutlak:</strong> Arsitektur sistem kami menjamin bahwa bisnis kompetitor atau tenant lain di platform Cooca mustahil dapat melihat ataupun mengakses data internal pembukuan dan margin keuntungan Anda.
            </p>
        </div>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-bold">C</span>
            Token Integrasi Media Sosial & Otomasi Konten
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Saat Anda menghubungkan akun TikTok Creator / TikTok for Business, Instagram Professional, atau Facebook Pages:
        </p>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li>User Access Token disimpan dalam format terenkripsi simetris (AES-256-CBC) dan tidak pernah dibagikan kepada pihak ketiga.</li>
            <li>Aplikasi Cooca hanya memanggil API resmi untuk mengunggah media video/gambar dan membaca statistik interaksi publik atas persetujuan Anda.</li>
            <li>Anda dapat mencabut otorisasi ini kapan saja melalui menu <em>Media Sosial > Akun Terhubung</em> di panel admin Cooca atau melalui pengaturan privasi di aplikasi TikTok/Meta masing-masing.</li>
        </ul>
    </section>
</div>
HTML;

        $privacyCustomer = <<<'HTML'
<div class="space-y-6">
    <div class="p-5 rounded-[20px] bg-gradient-to-br from-[#34C759]/10 via-[#34C759]/5 to-transparent border border-[#34C759]/20 space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
            <span>Bagian Khusus: Pelanggan & Pembeli Toko (Customer / End-Consumer)</span>
        </div>
        <p class="text-[13.5px] text-black/75 dark:text-white/75 leading-relaxed">
            Ketentuan berikut secara khusus mengatur data yang dikumpulkan dari Anda saat berbelanja di Toko Online Mitra Cooca, memesan makanan melalui Dining QR Code di meja restoran, atau menerima kuitansi digital.
        </p>
    </div>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-[12px] font-bold">A</span>
            Data Transaksi, Pengiriman & Logistik
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Saat Anda menyelesaikan pesanan di toko online atau kasir POS Cooca, data yang kami kumpulkan meliputi:
        </p>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li><strong>Data Kontak:</strong> Nama lengkap, nomor telepon seluler / WhatsApp (untuk pengiriman nota kuitansi, link tracking resi kurir, dan konfirmasi pesanan).</li>
            <li><strong>Data Alamat Fisik:</strong> Alamat jalan, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos tujuan pengiriman paket. Data ini diteruskan secara otomatis ke API agregator logistik (Biteship) dan mitra ekspedisi kurir yang Anda pilih.</li>
            <li><strong>Rincian Pesanan:</strong> Varian produk yang dibeli, catatan khusus meja (misal: bebas kacang / alergi), kuantitas, dan metode pengiriman yang dipilih.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-[12px] font-bold">B</span>
            Keamanan Pembayaran & Data Finansial
        </h3>
        <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-[13px] leading-relaxed space-y-1.5">
            <p><strong>Jaminan Tanpa Penyimpanan Data Kartu:</strong></p>
            <p>
                Saat Anda melakukan pembayaran menggunakan QRIS Dinamis, Virtual Account, atau E-Wallet, transaksi Anda diproses secara langsung oleh payment gateway resmi berlisensi Bank Indonesia (TriPay). Sistem Cooca <strong>TIDAK PERNAH</strong> mencatat, meminta, atau menyimpan nomor kartu debit/kredit, tanggal kadaluarsa kartu, kode CVV, ataupun PIN rahasia e-wallet Anda.
            </p>
        </div>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center text-[12px] font-bold">C</span>
            Notifikasi WhatsApp Transaksional & Kebijakan Anti-Spam
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Nomor WhatsApp Anda hanya digunakan untuk notifikasi layanan yang relevan:
        </p>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li>Kuitansi resmi digital sesaat setelah pembayaran berhasil di kasir atau toko online.</li>
            <li>Pembaruan status nomor resi pengiriman kurir dan lacak pesanan real-time.</li>
            <li>Kode sandi sekali pakai (OTP) saat login customer atau konfirmasi pengembalian barang.</li>
            <li>Anda berhak mematikan notifikasi pesan promosi sewaktu-waktu dengan membalas <em>"STOP"</em> atau menghubungi customer care toko bersangkutan.</li>
        </ul>
    </section>
</div>
HTML;

        LegalPage::updateOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title'            => 'Kebijakan Privasi & Pelindungan Data Pribadi',
                'subtitle'         => 'Komitmen kepatuhan UU PDP No. 27/2022, enkripsi data terisolasi, integrasi ekosistem pihak ketiga (TriPay, Biteship, WhatsApp, Meta, TikTok), dan jaminan tanpa penjualan data.',
                'meta_title'       => 'Kebijakan Privasi & Perlindungan Data Pribadi | Cooca Indonesia',
                'meta_description' => 'Kebijakan Privasi resmi Cooca mengenai pengumpulan data pemilik UMKM dan pelanggan toko, enkripsi AES-256, integrasi payment gateway, logistik, dan hak data UU PDP.',
                'content_general'  => $privacyGeneral,
                'content_owner'    => $privacyOwner,
                'content_customer' => $privacyCustomer,
                'version'          => '2.1',
                'effective_date'   => '2026-09-18',
                'is_published'     => true,
            ]
        );

        // 2. TERMS AND CONDITIONS
        $termsGeneral = <<<'HTML'
<section id="terms-acceptance" class="space-y-4">
    <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[13px] font-mono font-bold">01</span>
        Perjanjian Hukum Mengikat & Penerimaan Ketentuan
    </h2>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Syarat dan Ketentuan Layanan ini ("Ketentuan") merupakan perjanjian hukum yang sah dan mengikat antara Anda ("Pengguna", baik sebagai Pemilik Usaha/Merchant maupun Pembeli/Customer) dengan <strong>PT Cooca Digital Teknologi</strong> ("Cooca", "Kami") atas pemanfaatan situs web, aplikasi web, API, dan seluruh layanan ekosistem <strong>cooca.id</strong>.
    </p>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Perjanjian ini mengacu pada <strong>Pasal 1320 dan Pasal 1338 Kitab Undang-Undang Hukum Perdata (KUHPerdata)</strong> mengenai keabsahan perjanjian yang disepakati secara elektronik, serta <strong>Undang-Undang Nomor 11 Tahun 2008 jo. UU Nomor 1 Tahun 2024 tentang Informasi dan Transaksi Elektronik (UU ITE)</strong>.
    </p>
    <div class="p-4 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-[13px] leading-relaxed">
        <strong>Pernyataan Persetujuan:</strong> Dengan mendaftarkan akun, mengakses panel admin, melakukan transaksi belanja, atau menggunakan layanan Cooca, Anda menyatakan secara sadar bahwa Anda telah membaca, memahami, dan menyetujui seluruh ketentuan ini tanpa syarat.
    </div>
</section>

<section id="prohibited-activities" class="space-y-4 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
    <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[13px] font-mono font-bold">02</span>
        Kebijakan Barang Terlarang & Larangan Aktivitas Ilegal
    </h2>
    <p class="leading-relaxed text-black/75 dark:text-white/75">
        Platform Cooca ditujukan secara eksklusif untuk bisnis UMKM yang sah dan taat hukum di wilayah Republik Indonesia. Seluruh pengguna dilarang keras memperjualbelikan, mempromosikan, atau memfasilitasi barang dan jasa berikut:
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[13px] text-black/75 dark:text-white/75">
        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
            <strong class="text-[#FF3B30] dark:text-[#FF453A]">1. Narkotika & Zat Terlarang:</strong>
            <p class="text-black/60 dark:text-white/60">Narkotika, psikotropika, zat adiktif terlarang, atau obat keras tanpa resep dokter resmi/izin BPOM.</p>
        </div>
        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
            <strong class="text-[#FF3B30] dark:text-[#FF453A]">2. Senjata & Bahan Peledak:</strong>
            <p class="text-black/60 dark:text-white/60">Senjata api, replika airsoft gun tanpa izin, amunisi, bahan peledak, senjata tajam penyerang, atau kembang api ilegal.</p>
        </div>
        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
            <strong class="text-[#FF3B30] dark:text-[#FF453A]">3. Barang Tiruan & Pelanggaran HAKI:</strong>
            <p class="text-black/60 dark:text-white/60">Barang palsu/bajakan (KW), lisensi perangkat lunak ilegal, atau produk yang melanggar hak paten/merek orang lain.</p>
        </div>
        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
            <strong class="text-[#FF3B30] dark:text-[#FF453A]">4. Perjudian, Pornografi & Skema Penipuan:</strong>
            <p class="text-black/60 dark:text-white/60">Materi asusila, taruhan judi daring (judol), skema piramida cepat kaya (Ponzi), atau layanan ilegal lainnya.</p>
        </div>
    </div>
    <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-[12.5px] leading-relaxed">
        <strong>Tindakan Tegas:</strong> Cooca berhak secara sepihak membekukan akun bisnis, menahan pencairan dana, dan melaporkan bukti transaksi ke pihak berwajib (Kepolisian RI / PPATK) jika terbukti terjadi pelanggaran hukum.
    </div>
</section>

<section id="limitation-of-liability" class="space-y-4 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
    <h2 class="text-xl sm:text-2xl font-bold text-black dark:text-white flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[13px] font-mono font-bold">03</span>
        Batasan Tanggung Jawab & Force Majeure
    </h2>
    <p class="leading-relaxed text-black/75 dark:text-white/75 text-[13.5px]">
        Cooca berupaya sebaik mungkin menjaga ketersediaan layanan dengan target Service Level Agreement (SLA) 99.5% uptime. Namun, Cooca TIDAK BERTANGGUNG JAWAB atas:
    </p>
    <ul class="list-disc pl-6 space-y-1.5 text-[13px] text-black/70 dark:text-white/70">
        <li>Kerugian tidak langsung, kehilangan potensi laba, atau gangguan reputasi akibat keterlambatan operasional pihak ketiga (gangguan jaringan perbankan TriPay atau kendala lapangan kurir ekspedisi Biteship).</li>
        <li>Penolakan penayangan konten promosi atau sanksi akun yang dijatuhkan secara sepihak oleh algoritma TikTok atau Meta.</li>
        <li>Kejadian Kahar (<em>Force Majeure</em>) meliputi bencana alam, huru-hara, kebakaran server data center nasional, regulasi baru pemerintah, atau gangguan massal jaringan telekomunikasi nasional.</li>
        <li>Batas maksimum pertanggungjawaban ganti rugi finansial Cooca kepada Pengguna dibatasi maksimal sebesar biaya paket langganan 1 (satu) bulan terakhir yang dibayarkan oleh Pengguna bersangkutan.</li>
    </ul>
</section>
HTML;

        $termsOwner = <<<'HTML'
<div class="space-y-6">
    <div class="p-5 rounded-[20px] bg-gradient-to-br from-[#007AFF]/10 via-[#007AFF]/5 to-transparent border border-[#007AFF]/20 space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-bold bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF]">
            <i data-lucide="store" class="w-3.5 h-3.5"></i>
            <span>Ketentuan Khusus: Mitra Pemilik Usaha & UMKM (Owner / Merchant)</span>
        </div>
        <p class="text-[13.5px] text-black/75 dark:text-white/75 leading-relaxed">
            Sebagai pemilik toko, Anda terikat pada ketentuan operasional SaaS, sistem penampungan dana (escrow), tata kelola langganan, dan kepatuhan penjualan produk.
        </p>
    </div>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-bold">A</span>
            Paket Berlangganan SaaS, Kuota Storage & Token AI
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Akses ke fitur lengkap sistem operasi Cooca diatur berdasarkan ketentuan langganan berikut:
        </p>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li><strong>Siklus Penagihan:</strong> Langganan dibayar di muka (*prepaid*) dalam siklus Bulanan atau Tahunan sesuai katalog harga resmi.</li>
            <li><strong>Masa Tenggang (Grace Period):</strong> Jika langganan berakhir dan belum diperpanjang, sistem memberikan masa tenggang 7 (tujuh) hari kalender sebelum akun beralih ke mode baca-saja (*read-only*). Data bisnis Anda tetap aman dan tidak akan dihapus.</li>
            <li><strong>Kuota Tambahan:</strong> Top-up kuota penyimpanan media gambar/video dan token AI otomatis diakumulasikan ke akun bisnis Anda dan tidak memiliki masa kadaluarsa selama status paket langganan aktif.</li>
            <li><strong>Kebijakan Pengembalian Dana (Refund):</strong> Biaya langganan perangkat lunak yang telah terbayar bersifat *non-refundable* (tidak dapat dikembalikan) kecuali terjadi kegagalan sistem fatal dari sisi Cooca yang tidak dapat diselesaikan dalam 14 hari kerja.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-bold">B</span>
            Sistem Escrow Penampungan Dana & Pencairan (Settlement Payout)
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Melalui model payment gateway terpusat Cooca (TriPay Model B):
        </p>
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[13px]">
            <p class="text-black/80 dark:text-white/80">
                <strong>1. Penampungan Dana Aman:</strong> Seluruh pembayaran online dari pelanggan toko Anda ditampung sementara di rekening escrow platform Cooca sampai pesanan selesai diproses atau paket diterima oleh pembeli.
            </p>
            <p class="text-black/80 dark:text-white/80">
                <strong>2. Prosedur Settlement:</strong> Anda dapat mengajukan pencairan dana (*payout request*) ke rekening bank terdaftar Anda. Pencairan dana diproses pada hari kerja (H+1 Kliring Nasional / BI-FAST) setelah dipotong biaya Merchant Discount Rate (MDR) resmi penyedia payment gateway.
            </p>
            <p class="text-black/80 dark:text-white/80">
                <strong>3. Hak Penahanan Dana (Dispute & Chargeback):</strong> Cooca berhak menahan sementara pencairan dana jika terdapat komplain sengketa barang hilang/rusak yang belum diselesaikan dengan pembeli, atau adanya klaim chargeback perbankan.
            </p>
        </div>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[12px] font-bold">C</span>
            Kewajiban Pengemasan & Pengiriman Kurir Biteship
        </h3>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li>Pemilik Usaha wajib mengemas pesanan dengan aman (menggunakan bubble wrap, kardus pelindung, atau stiker fragile sesuai kategori barang).</li>
            <li>Pemilik Usaha wajib menempelkan label pengiriman resmi (thermal shipping label) ber-barcode yang dicetak langsung dari sistem Cooca.</li>
            <li>Pemilik Usaha wajib memastikan paket siap serah saat mitra kurir ekspedisi (Biteship pick-up driver) tiba di lokasi penjemputan toko.</li>
        </ul>
    </section>
</div>
HTML;

        $termsCustomer = <<<'HTML'
<div class="space-y-6">
    <div class="p-5 rounded-[20px] bg-gradient-to-br from-[#FF9500]/10 via-[#FF9500]/5 to-transparent border border-[#FF9500]/20 space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">
            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
            <span>Ketentuan Khusus: Pelanggan & Pembeli Toko (Customer / End-Consumer)</span>
        </div>
        <p class="text-[13.5px] text-black/75 dark:text-white/75 leading-relaxed">
            Ketentuan berikut secara khusus mengikat Anda saat melakukan transaksi pembelian di toko daring mitra Cooca, memesan makan di restoran melalui QR Dining POS, atau menggunakan fitur lacak pesanan.
        </p>
    </div>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center text-[12px] font-bold">A</span>
            Kewajiban Pembayaran & Masa Kedaluwarsa Tagihan
        </h3>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li>Pembeli wajib melunasi total tagihan (termasuk harga produk, ongkos kirim resmi kurir, dan biaya layanan platform) sesuai instruksi pembayaran pada layar checkout.</li>
            <li>Tagihan QRIS Dinamis dan Virtual Account memiliki batas waktu kedaluwarsa (misalnya 15 hingga 60 menit). Transaksi yang dibayarkan setelah tagihan kedaluwarsa tidak akan terproses otomatis dan pembeli wajib menghubungi customer support toko.</li>
            <li>Pembeli wajib memastikan nominal yang dibayarkan tepat hingga 3 digit terakhir jika menggunakan kode unik transfer.</li>
        </ul>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center text-[12px] font-bold">B</span>
            Kebijakan Pengembalian Produk (Retur) & Bukti Unboxing
        </h3>
        <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2 text-[13px]">
            <p class="text-black/80 dark:text-white/80">
                <strong>Syarat Wajib Video Unboxing:</strong> Untuk mengajukan komplain barang rusak, kurang, atau salah kirim, Pembeli WAJIB merekam video unboxing paket tanpa jeda (no pause) sejak paket masih tersegel utuh oleh kurir ekspedisi.
            </p>
            <p class="text-black/80 dark:text-white/80">
                <strong>Batas Waktu Klaim:</strong> Permohonan retur atau pengembalian dana harus diajukan maksimal 2 x 24 jam sejak sistem kurir Biteship mencatat status paket sebagai "Delivered / Diterima".
            </p>
            <p class="text-black/80 dark:text-white/80">
                <strong>Ganti Rugi Ekspedisi:</strong> Kerusakan yang terbukti murni diakibatkan oleh kelalaian pihak jasa ekspedisi kurir selama perjalanan akan diklaim melalui fasilitas asuransi pengiriman Biteship sesuai prosedur resmi ekspedisi terkait.
            </p>
        </div>
    </section>

    <section class="space-y-3">
        <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
            <span class="w-6 h-6 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center text-[12px] font-bold">C</span>
            Larangan Pesanan Fiktif & Penyalahgunaan Akun
        </h3>
        <p class="text-[13.5px] text-black/70 dark:text-white/70 leading-relaxed">
            Pembeli dilarang keras melakukan tindakan manipulasi sistem, meliputi:
        </p>
        <ul class="list-disc pl-6 space-y-1 text-[13px] text-black/70 dark:text-white/70">
            <li>Melakukan pesanan fiktif (*fake order*) yang merugikan stok dan operasional pemilik usaha.</li>
            <li>Memberikan alamat palsu atau nomor WhatsApp fiktif yang menyebabkan kegagalan pengiriman kurir berulang kali.</li>
            <li>Menyalahgunakan bug sistem atau celah keamanan untuk memperoleh diskon atau saldo tidak sah.</li>
            <li>Cooca berhak memblokir nomor telepon dan alamat IP pembeli yang terbukti melakukan pelanggaran dari seluruh ekosistem toko binaan Cooca.</li>
        </ul>
    </section>
</div>
HTML;

        LegalPage::updateOrCreate(
            ['slug' => 'terms-conditions'],
            [
                'title'            => 'Syarat & Ketentuan Layanan (Terms of Service)',
                'subtitle'         => 'Perjanjian kontraktual penggunaan ekosistem Cooca, hak dan kewajiban Mitra Usaha & Pelanggan, tata kelola langganan SaaS, sistem escrow payment gateway, logistik, dan batas tanggung jawab.',
                'meta_title'       => 'Syarat & Ketentuan Layanan (Terms of Service) | Cooca Indonesia',
                'meta_description' => 'Syarat dan Ketentuan resmi penggunaan platform Cooca, pemisahan hak kewajiban Owner UMKM dan Pembeli, aturan settlement TriPay, pengiriman Biteship, dan resolusi sengketa.',
                'content_general'  => $termsGeneral,
                'content_owner'    => $termsOwner,
                'content_customer' => $termsCustomer,
                'version'          => '2.1',
                'effective_date'   => '2026-09-18',
                'is_published'     => true,
            ]
        );
    }
}
