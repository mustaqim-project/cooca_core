# Panduan Induk Sistem Cooca ERP & POS (System Guide)

> **Dokumentasi Tingkat Tertinggi (Layer 3: Curated Master System Manual)**  
> **Target Pembaca:** Pemilik Usaha (Business Owner), Tim Produk, Pengembang Perangkat Lunak (Developer), dan AI Development Agent.  
> **Versi Sistem:** Cooca Enterprise ERP & POS v2.0 (Laravel 11 + DDD 33 Packages + Apple HIG Bento UI)  
> **Prinsip Utama:** `Clarity → Deference → Depth → Empathy → Simplicity`

---

## 📑 Daftar Isi Cepat

1. [Ikhtisar Sistem & Filosofi Desain](#1-ikhtisar-sistem--filosofi-desain)
2. [Konsep Inti & Arsitektur Multi-Tenancy](#2-konsep-inti--arsitektur-multi-tenancy)
3. [Panduan Pemilik Usaha (Business Owner Operations Manual)](#3-panduan-pemilik-usaha-business-owner-operations-manual)
   - [3.1 Menentukan Modal & Harga Jual Ilmiah (Costing & HPP)](#31-menentukan-modal--harga-jual-ilmiah-costing--hpp)
   - [3.2 Operasional Kasir Harian (Terminal Kasir POS)](#32-operasional-kasir-harian-terminal-kasir-pos)
   - [3.3 Manajemen Stok & Penerimaan Bahan (Gudang & GR)](#33-manajemen-stok--penerimaan-bahan-gudang--gr)
   - [3.4 Mengembangkan Kanal Penjualan Online (Storefront)](#34-mengembangkan-kanal-penjualan-online-storefront)
   - [3.5 Pembukuan Otomatis Tanpa Pusing Akuntansi (Keuangan)](#35-pembukuan-otomatis-tanpa-pusing-akuntansi-keuangan)
   - [3.6 Manajemen Katalog Produk, Resep (BOM), & Modifiers (Varian)](#36-manajemen-katalog-produk-resep-bom--modifiers-varian)
   - [3.7 Manajemen Bahan Baku, Pemasok, & Layanan Jasa Bebas Stok](#37-manajemen-bahan-baku-pemasok--layanan-jasa-bebas-stok)
   - [3.8 Toko Online (Storefront Hub) & Website Landing Page Studio](#38-toko-online-storefront-hub--website-landing-page-studio)
   - [3.9 Saluran WhatsApp Resmi (WhatsApp Cloud API Meta)](#39-saluran-whatsapp-resmi-whatsapp-cloud-api-meta)
   - [3.10 Pengelolaan Media Sosial Terpadu (Meta & TikTok)](#310-pengelolaan-media-sosial-terpadu-meta--tiktok)
4. [Panduan Rekayasa Developer & AI Agent (Engineering Blueprint)](#4-panduan-rekayasa-developer--ai-agent-engineering-blueprint)
   - [4.1 Struktur 33 Domain Packages DDD](#41-struktur-33-domain-packages-ddd)
   - [4.2 Aturan Scoping Tenant & Proteksi Keamanan](#42-aturan-scoping-tenant--proteksi-keamanan)
   - [4.3 Mesin Otomasi Latar Belakang (Auto-Journal & Auto-Stock)](#43-mesin-otomasi-latar-belakang-auto-journal--auto-stock)
   - [4.4 Protokol Verifikasi & Kesiapan Produksi (100% Zero-Error Mandate)](#44-protokol-verifikasi--kesiapan-produksi-100-zero-error-mandate)
   - [4.5 Protokol Dokumentasi Berkelanjutan Simultan (AiWorkHistory.md + SYSTEM_GUIDE.md)](#45-protokol-dokumentasi-berkelanjutan-simultan-aiworkhistorymd--system_guidemd)
   - [4.6 Arsitektur Multi-Tenant WhatsApp Cloud API](#46-arsitektur-multi-tenant-whatsapp-cloud-api)
   - [4.7 Arsitektur Media Sosial Omnichannel (Meta & TikTok Open API v2)](#47-arsitektur-media-sosial-omnichannel-meta--tiktok-open-api-v2)
5. [Matriks Penelusuran Pengetahuan (Traceability Matrix)](#5-matriks-penelusuran-pengetahuan-traceability-matrix)

---

## 1. Ikhtisar Sistem & Filosofi Desain

Cooca adalah sistem operasi bisnis terpadu (*All-in-One Business OS*) yang dirancang khusus untuk memadukan kekuatan ERP kelas enterprise dengan kesederhanaan antarmuka yang sangat ramah pengguna (*ultra user-friendly*).

### Filosofi Desain "Apple Human Interface Guidelines & Bento Grid UI"
Aplikasi ini dirancang untuk dapat dioperasikan secara percaya diri oleh **generasi Boomers (usia 50–65+ tahun) dan milenial akhir yang gaptek (tidak paham teknis)**:
* **Antarmuka Tanpa Panduan (*Zero-Manual UI*):** Saat pengguna membuka aplikasi, mereka langsung paham apa yang harus dilakukan tanpa perlu membaca buku manual panjang.
* **Ergonomi Jempol, Tata Letak Anti-Pecah & Aksesibilitas Visual:**
  - **Zero Horizontal Overflow:** Arsitektur fluid container (`w-full max-w-full min-w-0 truncate`) yang menjamin tidak ada pergeseran layar ke samping pada smartphone 360px–430px.
  - **Matriks Tipografi Dinamis Lintas Perangkat:** Skala font terkalibrasi presisi untuk Mobile, Tablet, dan Desktop (acuan resmi di `docs/prompt.md` dan `AGENTS.md`).
  - Touch targets tombol aksi utama berukuran minimal **48px hingga 52px** agar tidak meleset saat ditekan di layar ponsel.
  - Ukuran font kolom input minimal **16px** (`text-[16px] sm:text-[14px]`) untuk mencegah auto-zoom browser iOS Safari yang merusak tampilan.
  - Sudut membulat organik (*squircle* `rounded-[20px]`) dan border hairline lembut yang memanjakan mata.
* **Format Ribuan Otomatis:** Mengetik nominal uang otomatis menghasilkan tanda pemisah ribuan (`Rp 150.000`), mencegah kekeliruan mengetik nol berlebih.
* **Standar Modal Pop-Up Full Layout XXL & Responsif Multi-Device:**
  - Operasi Create, Show (Detail), dan Edit pada seluruh halaman index mengadopsi arsitektur **Modal-First** tanpa berpindah halaman (*Zero Navigation Jumps*), menjaga filter dan posisi pagination tetap utuh.
  - **Desktop (>= 1024px):** Menggunakan **Full Layout XXL Centered Bento Dialog** (`max-w-5xl` s/d `max-w-7xl` / `max-w-[95vw] rounded-[24px] max-h-[90vh]`) yang lapang, mampu menampung grid bento multi-kolom (8 kolom form/tabel utama + 4 kolom metrik ringkasan) tanpa berdesakan.
  - **Tablet (640px – 1023px):** Centered Responsive Bento Modal (`max-w-3xl` s/d `max-w-4xl rounded-[22px] max-h-[90vh]`) dengan tata letak 2 kolom seimbang dan touch target 44px–48px.
  - **Mobile (< 640px):** Apple Full-Responsive Bottom Sheet (`w-full inset-x-0 bottom-0 rounded-t-[28px] max-h-[94vh]`) meluncur dari bawah lengkap dengan indikator grab bar Apple, input font minimal 16px anti-auto-zoom iOS, dan sticky bottom action bar menempel jempol.
* **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*):** Di setiap aksi penting atau dialog konfirmasi, sistem selalu menyertakan pesan penenang menggunakan font icon Lucide `info` (bebas emoji):  
  *“Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.”*

---

## 2. Konsep Inti & Arsitektur Multi-Tenancy

* **Multi-Tenancy Berbasis Shared Database:** Seluruh bisnis berbagi basis data yang sama namun terisolasi secara mutlak di tingkat query database menggunakan `Context::requireBusiness()` dan foreign key `business_id`.
* **Identitas Global Customer:** Pelanggan storefront publik memiliki satu akun global (`GlobalCustomer`) yang dapat digunakan untuk berbelanja di berbagai toko UMKM berbeda, dengan keranjang belanja (`CustomerCart`) yang tetap terisolasi penuh per tenant.
* **Jaminan Integritas Finansial Non-Destruktif:** Rumus subtotal, pajak PPN, diskon, HPP, saldo kas berjalan, dan keseimbangan jurnal akuntansi bersifat mutlak dan tidak boleh diubah secara destruktif.

---

## 3. Panduan Pemilik Usaha (Business Owner Operations Manual)

### 3.1 Menentukan Modal & Harga Jual Ilmiah (Costing & HPP)
* **Kapan Digunakan?** Saat Anda ingin meluncurkan menu baru, membuat produk kemasan, atau mengevaluasi apakah harga jual saat ini masih memberikan keuntungan layak di tengah kenaikan harga pasar.
* **Cara Kerjanya:**
  1. Buka menu **Kalkulator HPP**.
  2. Masukkan bahan baku yang digunakan beserta takarannya (misal: *Kopi 18 gram, Susu 120 ml, Cup 1 pcs*).
  3. Masukkan biaya upah kerja karyawan per porsi dan estimasi biaya listrik/alat.
  4. Tentukan target keuntungan (misal: *Margin Kotor 60%*).
  5. Sistem langsung menyajikan rekomendasi harga jual resmi dan titik impas (**BEP**).
* **Dampak ke Bisnis:** Anda dapat langsung menekan tombol `[ Terapkan ke Produk ]` agar kasir dan toko online langsung menggunakan harga tersebut secara otomatis.

### 3.2 Operasional Kasir Harian (Terminal Kasir POS)
* **Kapan Digunakan?** Setiap hari selama jam operasional toko berlangsung untuk melayani antrean pembeli di kasir.
* **Alur Standar Kasir:**
  1. **Buka Shift:** Masukkan modal awal kas kecil di laci kasir (*Float Cash*).
  2. **Transaksi Cepat:** Sentuh foto produk di katalog bento, pilih topping/level pedas (modifier), dan pilih metode bayar (Tunai, QRIS, atau Kasbon).
  3. **Cetak Struk & Kirim WA:** Tekan `[ 📄 Simpan & Cetak Struk ]`. Printer thermal mencetak struk fisik seketika, dan WhatsApp pelanggan menerima link struk digital resmi.
  4. **Tutup Shift:** Hitung uang fisik di laci kasir di akhir hari. Sistem membandingkannya dengan catatan sistem dan mencatat selisih kas secara transparan.
* **Dampak ke Bisnis:** Kasir tidak bisa membatalkan transaksi (void) atau mengambil uang secara diam-diam karena tindakan berisiko dilindungi **PIN Supervisor**.

### 3.3 Manajemen Stok & Penerimaan Bahan (Gudang & GR)
* **Kapan Digunakan?** Saat pasokan bahan baku atau stok barang dari supplier datang ke toko/gudang.
* **Cara Kerjanya:**
  1. Buka menu **Penerimaan Barang (*Goods Receipt*)**.
  2. Cocokkan fisik barang yang datang dengan Surat Pesanan (PO).
  3. Simpan penerimaan.
* **Otomasi Latar Belakang:** Sesaat setelah disimpan, stok bertambah seketika, HPP modal rata-rata diperbarui otomatis, dan tagihan hutang supplier (AP) langsung tercatat di menu keuangan tanpa perlu input ulang.

### 3.4 Mengembangkan Kanal Penjualan Online (Storefront)
* **Kapan Digunakan?** Membagikan link toko online Anda (`cooca.id/b/nama-toko-anda`) ke media sosial, Instagram Bio, atau status WhatsApp.
* **Cara Kerjanya:**
  - Pembeli memilih produk, memasukkan ke keranjang, dan melakukan checkout mandiri.
  - Pembeli mentransfer dana dan mengunggah foto bukti bayar.
  - Anda menerima notifikasi di handphone dan cukup menekan `[ ✅ Verifikasi & Proses Pesanan ]`.
* **Dampak ke Bisnis:** Toko Anda melayani pesanan 24 jam non-stop tanpa membuat pembeli menunggu balasan chat yang lama.

### 3.5 Pembukuan Otomatis Tanpa Pusing Akuntansi (Keuangan)
* **Kapan Digunakan?** Setiap saat Anda ingin melihat posisi kesehatan uang toko (Laba Bersih, Uang di Kasir, Saldo di Bank, Piutang Pembeli, dan Mutasi Gateway).
* **Keunggulan Cooca:** Anda **tidak perlu mengerti debit, kredit, atau kode akun**. Sistem secara otonom menjalankan `AutoJournalService` setiap kali transaksi kasir atau pembayaran hutang terjadi.
* **Fitur Utama Pembukuan & Rekonsiliasi Terpadu:**
  - **Konsolidasi Omnichannel Laba Rugi:** Laporan Laba Rugi secara otomatis menggabungkan seluruh saluran penjualan: Kasir POS, Faktur Penjualan (Invoicing), dan Toko Online Storefront (`CommerceOrder`). Pendapatan kotor, ongkos kirim, diskon, dan HPP aktual berbasis snapshot modal tersaji transparan.
  - **Arus Kas Akurat Bebas Hitung Ganda (*Zero Double-Counting*):** Penerimaan kas dari pesanan online dan POS diisolasi dari transaksi kas langsung generik, menjamin saldo kas akhir di laporan arus kas 100% konsisten dengan fisik riil.
  - **Pemisahan Fisik Laci Kasir POS vs Digital Gateway:** Pada saat kasir menutup shift, sistem secara ketat membedakan omzet tunai (`cash_sales`) dengan penjualan non-tunai/gateway (`gateway_sales`). Jumlah uang fisik yang diharapkan di laci kasir (*expected cash*) murni dihitung dari uang modal awal ditambah penjualan tunai riil, sehingga kasir tidak panik mencari fisik uang transaksi digital QRIS.
  - **Rekonsiliasi Pencairan Gateway (*Gateway Settlement Hub*):** Modul rekonsiliasi dana pencairan TriPay di menu Keuangan. Saat dana dari TriPay cair ke rekening bank operasional UMKM, sistem mencatat jurnal ganda secara otomatis: mendebit Rekening Bank (`1-1002`), mendebit Beban MDR Gateway (`6-6003`), dan mengkredit Akun Kliring Gateway (`1-1005`), sekaligus menandai transaksi POS dan toko online terkait sebagai *reconciled*.
  - **Audit Trail Callback Webhook & Tombol Failover Sinkronisasi:** Jejak seluruh webhook gateway tercatat pada `payment_gateway_callback_logs` dan jika ada notifikasi gateway terlambat, kasir/pemilik dapat menekan tombol *"Cek & Sinkronkan Status TriPay"* untuk verifikasi instan.
 
### 3.6 Manajemen Katalog Produk, Resep (BOM), & Modifiers (Varian)
* **Kapan Digunakan?** Mengelola seluruh daftar dagangan toko, baik barang jadi (retail/F&B), bahan mentah, jasa/layanan, formula resep produksi (BOM), hingga pilihan varian/topping.
* **Fitur & Keunggulan Alur Kerja:**
  - **Apple Segmented Control:** Berpindah seketika antara *Barang Fisik (Katalog)*, *Jasa & Layanan*, dan *Varian & Modifiers* melalui tab tersegmentasi yang bersih dan intuitif.
  - **Full Layout XXL Modal (Zero Navigation Jump):** Menambah atau mengedit produk dilakukan langsung di jendela pop-up XXL 12-kolom terpadu tanpa pernah meninggalkan daftar katalog atau mereset filter.
  - **Inline Quick-Add AJAX `[ + ]`:** Menambah kategori atau satuan baru langsung dari samping dropdown tanpa reload halaman atau kehilangan data yang sedang diketik.
  - **Manajemen Kanal Terpadu:** Pengaturan visibilitas kasir POS, Surat Pesanan (Sales Order), Toko Online Storefront, dan Pre-Order dapat disesuaikan per produk dengan saklar instan.
  - **Resep Produksi (BOM) & Modifiers:** Setiap produk jadi dapat dihubungkan ke resep bahan baku sehingga stok gudang terpotong otomatis saat kasir memproses pesanan.
  - **Penenang Jiwa Saat Menghapus:** Dialog konfirmasi hapus selalu memberikan jaminan kepastian: riwayat nota kasir dan jurnal pembukuan masa lalu yang menggunakan produk tersebut tetap aman tersimpan.

### 3.7 Manajemen Bahan Baku, Pemasok, & Layanan Jasa Bebas Stok
* **Kapan Digunakan?** Mengelola seluruh rantai pasok bahan baku mentah (untuk usaha manufaktur/kuliner) serta tarif layanan jasa bebas stok (untuk usaha salon, bengkel, klinik, servis, dsb.).
* **Fitur & Keunggulan Alur Kerja:**
  - **Efisiensi Rendemen & Susut (Yield & Waste):** Menghitung harga pokok akuisisi riil per gram/ml secara matematis dengan memperhitungkan faktor susut bahan dan ongkos kirim.
  - **Triple Inline Quick-Add AJAX `[ + ]`:** Menambahkan Kategori Bahan, Satuan, dan Supplier Pemasok secara instan dari form bahan baku tanpa memuat ulang layar atau mereset draf isian.
  - **Layanan Jasa Bebas Stok (Selalu Siap Jual):** Layanan jasa tidak memerlukan stok gudang fisik dan dapat langsung dipanggil pada transaksi kasir POS atau faktur penjualan.
  - **Navigasi Terpadu Apple Segmented Control:** Berpindah mulus antara *Barang Fisik*, *Jasa & Layanan*, dan *Varian & Modifiers* dalam 1 ketukan.
  - **Zero-Navigation Jump pada Edit Layanan:** Pengeditan jasa menggunakan instant client-side modal tanpa reload parameter URL `?edit=<id>`.
  - **Penenang Jiwa Saat Menghapus:** Menjamin keamanan data historis masa lalu pada setiap dialog konfirmasi hapus bahan maupun jasa.

### 3.8 Toko Online (Storefront Hub) & Website Landing Page Studio
* **Kapan Digunakan?** Saat Anda ingin mempublikasikan profil bisnis digital, menampilkan etalase produk & layanan ke publik, menerima pesanan mandiri pelanggan via web, menetapkan ongkir kurir toko, serta mengelola reservasi meja/jadwal.
* **Fitur Utama & Keunggulan Operasional:**
  - **Bento Cockpit Pengaturan Toko:** Memantau visibilitas etalase, status direktori `/jelajah`, rekening aktif, serta mode transaksi (Langsung, Terjadwal, PO B2B, Reservasi).
  - **Integritas Saklar Non-Destruktif:** Setiap switch etalase dikawal input hidden deterministik agar status penonaktifan tersimpan sempurna tanpa merusak pengaturan lainnya.
  - **Dialog Konfirmasi Penenang Jiwa:** Hapus rekening pembayaran, hapus aturan ongkir, dan pembatalan reservasi tamu dilindungi dialog konfirmasi Apple Alert yang menjamin transaksi dan riwayat masa lalu tetap aman tercatat.
  - **Verifikasi Pembayaran 1-Klik:** Verifikasi bukti transfer pelanggan secara visual di modal Apple Alert dengan alokasi pemotongan stok otomatis yang sah di pembukuan.
  - **Aturan Ongkir Adaptif:** Menghitung ongkos kirim berbasis tarif flat, radius jarak (KM), dan promo bebas ongkir otomatis berdasarkan ambang belanja minimum.
  - **CMS Studio Landing Page 11 Bagian:** Mengatur identitas brand, warna aksen kustom, banner hero, ulasan pelanggan, galeri foto suasana, jam operasional, dan optimasi SEO Google dengan dukungan preset instan 25 industri.
  - **Pengalaman Halaman Publik Pelanggan (`/b/{slug}` & `business_landing.blade.php`):** 
    - **Apple HIG Bento Modal Architecture:** Modal Checkout (`max-w-5xl`), Reservasi (`max-w-4xl`), Request Order (`max-w-4xl`), dan Customer PO (`max-w-4xl`) mengadopsi tata letak responsif: Full-layout 2 kolom Bento Dialog di desktop (kolom kiri untuk input formulir/jadwal & kolom kanan untuk ringkasan biaya/metode bayar/submit) dan native Apple Bottom Sheet meluncur dari bawah di mobile (`items-end`, `rounded-t-[28px]`, grab bar halus).
    - **Anti-AI-Template & Tipografi Murni:** Menggunakan *Pure Typographic Overline* tanpa badge pill dekoratif, eliminasi ikon clutters (seperti sparkles), dan penggunaan kata kerja aksi padat (*Pesan*, *Reservasi*, *WhatsApp*).
    - **Ergonomi Aksesibilitas:** Seluruh input formulir berukuran minimal 16px di mobile anti-auto-zoom iOS, touch target 48px–52px, tombol mengambang WhatsApp terbebas dari fake unread dots, dan safe-area padding bawah (`pb-28 sm:pb-32 md:pb-28`).
    - **Anti-FOUC Theme Synchronizer:** Inisialisasi tema instan berbasis database CMS toko (`var isLandingDark`) yang mencegah kedipan kontras saat halaman dimuat.
    - **Model Referensi FnB & Katering (Dapur Sedap Rasa):** Melalui `DapurSedapRasaSeeder.php` (`/dapur-sedap-rasa`), platform mendemonstrasikan kapabilitas ganda operasional: Order Offline (Kasir POS, 12 Meja Dine-in Indoor/Outdoor/VIP, Takeaway) dan Pre-Order (PO) Online Katering (Nasi Box, Tumpeng, Prasmanan, kuota pesanan, batch schedule delivery, transfer bank BCA/Mandiri & QRIS).

### 3.9 Saluran WhatsApp Resmi (WhatsApp Cloud API Meta)
* **Kapan Digunakan?** Saat Anda ingin mengirimkan struk kasir digital otomatis, faktur tagihan (invoice), kode OTP, dan notifikasi pesanan resmi langsung ke nomor WhatsApp pelanggan tanpa risiko blokir nomor.
* **Fitur Utama & Keunggulan Operasional:**
  - **Onboarding Mandiri 1-Klik (Meta Embedded Signup):** Merchant cukup menghubungkan akun WhatsApp Business Facebook mereka melalui jendela pop-up resmi Meta tanpa perlu konfigurasi token manual yang rumit.
  - **Identitas Bisnis Resmi & Verified Badge:** Menampilkan nama bisnis resmi (`verified_name`) dan centang hijau Meta di chat pelanggan, meningkatkan kepercayaan dan kredibilitas UMKM.
  - **Pengiriman Struk POS & Invoice Terstruktur:** Menggunakan template pesan resmi Meta yang disetujui, dilengkapi media gambar struk, rincian biaya, serta tombol tautan langsung ke struk digital interaktif.
  - **Notifikasi Otomatis & Pemantauan Status Transparan:** Status pengiriman pesan terperinci secara live (`terkirim`, `diterima`, `dibaca`, `gagal`) dengan pemantauan rating kualitas nomor (GREEN/YELLOW/RED) dan batas kuota pesan (tier).

### 3.10 Pengelolaan Media Sosial Terpadu (Meta & TikTok)
* **Kapan Digunakan?** Saat pemilik toko ingin mengelola dan mempublikasikan materi promosi, video produk, dan foto katalog ke Facebook Page, Instagram Bisnis, Threads, dan TikTok secara serentak dari satu dashboard COOCA.
* **Fitur Utama & Keunggulan Operasional:**
  - **Koneksi Akun 1-Klik Resmi:** Menghubungkan akun Facebook, Instagram, Threads, dan TikTok melalui dialog otorisasi OAuth 2.0 resmi (Meta Login & TikTok Developer Platform) dengan pembaruan token otomatis (*auto-refresh*).
  - **Composer Omnichannel Terpadu:** Membuat 1 konten promosi dan mendistribusikannya ke berbagai akun media sosial sekaligus, dengan pratinjau langsung (*live preview*) dan kustomisasi caption spesifik per kanal.
  - **Instagram Carousel 2–10 Media dengan Drag & Drop:** Pengunggahan korsel foto/video Instagram multi-item dengan fitur penyusunan ulang urutan slide (*reordering tray*) yang mulus.
  - **Aturan Bisnis COOCA (Maksimal 5 Tagar Unik):** Menegakkan batas maksimal 5 tagar per postingan/kanal secara otomatis dengan deduplikasi case-insensitive dan indikator badge live (`Tagar: X / 5`) demi memaksimalkan jangkauan algoritma dan estetika feed.
  - **Penyimpanan Server Bebas Beban (*Storage Auto-Purge*):** Berkas video/foto yang diunggah langsung dibersihkan permanen dari server COOCA segera setelah postingan sukses terbit ke API platform.
  - **Kalender Konten & Analitik:** Tampilan kalender jadwal tayang bulanan dan pemantauan metrik impresi, jangkauan (*reach*), interaksi, dan komentar.

---

## 4. Panduan Rekayasa Developer & AI Agent (Engineering Blueprint)

### 4.1 Struktur 33 Domain Packages DDD
Logika bisnis utama tidak ditempatkan di Controller, melainkan pada domain package di `app/Domain/`:
* Controller bertindak sebagai *HTTP transport layer* (validasi request, otorisasi, dan format respon).
* Domain Service mengeksekusi logika bisnis inti dalam transaksi database atomik (*DB::transaction*).
* Model Eloquent menangani relasi data, mutator, casting, dan event lifecycle.

### 4.2 Aturan Scoping Tenant & Proteksi Keamanan
* **Aturan Scoping Mutlak:** Setiap query entitas tenant WAJIB terikat pada `$business->id` atau `Context::requireBusiness()`. Dilarang melakukan query un-scoped seperti `Product::all()`.
* **Proteksi IDOR Portal Pelanggan:** Akses `/customer/orders/{id}` WAJIB memverifikasi bahwa ID customer pada pesanan identik dengan identitas yang diautentikasi oleh guard `auth:customer`.
* **Proteksi PIN Kasir:** Verifikasi PIN supervisor menggunakan hash Bcrypt dan dibatasi rate limit (*throttle: 5, 1 menit*).

### 4.3 Mesin Otomasi Latar Belakang (Auto-Journal & Auto-Stock)
* **AutoJournalService:** Mengkonversi transaksi kasir, pelunasan AP/AR, dan mutasi kas menjadi jurnal memorial berimbang ($\sum \text{Debit} = \sum \text{Kredit}$).
* **Auto-BOM Engine:** Mengurangi saldo stok bahan baku mentah secara atomik (`decrement`) saat pesanan kasir berstatus `paid`.

### 4.4 Protokol Verifikasi & Kesiapan Produksi (100% Zero-Error Mandate)
Sebelum pekerjaan rekayasa dianggap selesai:
1. **Uji Sintaks:** `php -l <file>` wajib bebas error di seluruh file PHP yang dimodifikasi.
2. **Uji Rute:** `php artisan route:list` wajib terdaftar tanpa route collision.
3. **Pengujian Otomatis:** `php artisan test` wajib lolos 100% (0 failure, 0 error).
4. **Kesiapan Source Code Produksi:**
   - Bebas mock data, stub dummy, atau bypass OTP sementara.
   - Bersih dari fungsi debugging mentah (`dd()`, `dump()`, `ray()`, `var_dump()`, `console.log()`).
   - Aset frontend terkompilasi produksi (`npm run build`) dan cache teroptimasi.
   - Pembersihan data testing: record dummy dan file sampah uji coba dibersihkan tuntas dari database operasional.

### 4.5 Protokol Dokumentasi Berkelanjutan Simultan (AiWorkHistory.md + SYSTEM_GUIDE.md)
* **Mandat Mutlak Pembaruan Bersamaan:** Setiap kali AI Agent atau engineer menyelesaikan tugas rekayasa dan mencatatkan riwayat di `docs/AiWorkHistory.md`, **WAJIB secara simultan memperbarui `docs/SYSTEM_GUIDE.md` (dan dokumen terkait di `docs/system/`)**.
* **Alasan & Filosofi:** `AiWorkHistory.md` adalah catatan kronologis masa lalu (audit trail & historical context), sedangkan `SYSTEM_GUIDE.md` adalah pedoman hidup (*Living Master Guide*) bagi Business Owner, Developer, QA, dan AI Agent. Dilarang keras hanya memperbarui history tanpa menyelaraskan System Guide.
* **Status Penyelesaian:** Tugas yang hanya mencatatkan history di `AiWorkHistory.md` tanpa menyelaraskan `SYSTEM_GUIDE.md` diklasifikasikan sebagai **BELUM SELESAI (INCOMPLETE / PARTIAL)** dan tidak dapat dinyatakan `VERIFIED` atau `COMPLETED`.

### 4.6 Arsitektur Multi-Tenant WhatsApp Cloud API
* **Pemisahan Kredensial Multi-Tenant:** Setiap merchant memiliki satu rekaman data pada tabel `whatsapp_accounts` yang menyimpan `waba_id`, `phone_number_id`, `phone_number`, dan `access_token`.
* **Enkripsi Otomatis Atribut Sensitif:** Kolom `access_token` dienkripsi secara otomatis pada level basis data menggunakan native Laravel cast `'access_token' => 'encrypted'` (AES-256-CBC dengan `APP_KEY`), mencegah kebocoran kredensial saat backup basis data.
* **Asynchronous Webhook Processing via Redis:** Endpoint webhook Meta (`/api/v1/wa/meta/webhook`) segera memvalidasi tanda tangan kriptografis HMAC-SHA256 (`X-Hub-Signature-256`) menggunakan `hash_equals()` dan mendispatch event ke antrean Redis Job (`ProcessWhatsAppWebhookJob`), mengembalikan HTTP 200 dalam waktu <200ms untuk memenuhi SLA Meta.
* **Isolasi Pemrosesan Event:** `WhatsAppWebhookService` memetakan `phone_number_id` yang tertera pada metadata webhook ke `business_id` tenant merchant secara presisi, menjamin pesan masuk dan pembaruan status tidak pernah tertukar lintas tenant.
* **Client HTTP Andal dengan Retry Logic:** `WhatsAppClient` membungkus pemanggilan Graph API v21.0 dengan retry otomatis (hingga 3 kali percobaan) pada galat jaringan transien (HTTP 429 Rate Limit dan HTTP 5xx Server Error Meta) dan audit logging terstruktur per-tenant.

### 4.7 Arsitektur Media Sosial Omnichannel (Meta & TikTok Open API v2)
* **Zero .env Architecture & Database-Driven Settings:** Seluruh konfigurasi platform (`social_media_app_id`, `social_media_app_secret`, `instagram_app_id`, `instagram_app_name`, `instagram_app_secret`, `instagram_access_token`, `tiktok_client_key`, `tiktok_client_secret`) dikelola eksklusif melalui antarmuka Superadmin `resources/views/admin/settings` (Tab "Media Sosial") dan tersimpan di tabel `system_settings` dengan flag rahasia `is_secret = true`, tanpa menyentuh file `.env`.
* **Official Instagram Platform API & Cerdas Dual-Host Routing:** Integrasi resmi Instagram Graph API v21.0 (`Cooca-IG`) mendukung akun Bisnis/Kreator (`@cooca.indonesia`). `MetaSocialMediaClient` mengimplementasikan deteksi prefiks token (`IGAA...`) untuk otomatis mengalihkan host ke `https://graph.instagram.com/{version}/...` alih-alih `https://graph.facebook.com/{version}/...`, mencegah kegagalan OAuth 190 sambil tetap memelihara kompatibilitas dengan Facebook Page token.
* **Enkripsi Kredensial & Auto-Refresh Token:** `access_token` dan `refresh_token` pada `social_media_accounts` dienkripsi simetris menggunakan Eloquent encrypted cast. Token TikTok (berlaku 24 jam) diperiksa secara berkala dan di-refresh otomatis sebelum eksekusi postingan.
* **Pola Desain Strategy & Kontrak Provider:** `SocialMediaProviderInterface` diimplementasikan oleh `MetaProvider` (Facebook, Instagram, Threads) dan `TikTokProvider` (Direct Post Video & Photo Mode). Resolusi provider bersifat decoupled melalui `SocialMediaManager`.
* **Validasi Konten Sentral & Aturan 5 Tagar:** `SocialMediaContentValidator` memvalidasi batas karakter per platform (FB 63.206, IG 2.200, Threads 500, TikTok 2.200), aturan korsel Instagram (2–10 media), serta aturan wajib COOCA maksimal 5 unique hashtags dengan normalisasi case-insensitive.
* **Queue-First Distributed Publishing:** Publikasi multi-target didelegasikan ke antrean `PublishSocialMediaTargetJob` dengan retry berjenjang (*exponential backoff* 10s, 30s, 60s). Kegagalan satu target (mis. TikTok) tidak membatalkan keberhasilan target lain (mis. Instagram), dan target gagal dapat di-retry secara independen.

### 4.8 Arsitektur Pre-Order & Batch Scheduling Dinamis Terparameterisasi
* **Strict Batch vs Flexible Scheduling:** Parameter `allow_custom_date` (boolean) pada `commerce_store_settings` memungkinkan merchant mengunci pemesanan hanya pada tanggal batch yang ditentukan (menyembunyikan datepicker bebas pada checkout modal dan memvalidasi pesanan di server-side). Jika aktif, pembeli tetap diberikan keleluasaan memilih tanggal kalender mandiri.
* **Dual Quota Metric (`quantity` vs `orders`):** Kapasitas kuota (`daily_order_quota`) mendukung dua mode kalkulasi melalui kolom `quota_metric`:
  - `quantity`: Kuota dihitung berdasarkan total kuantitas unit produk yang dipesan (`SUM(commerce_order_items.quantity)`), cocok untuk kapasitas dapur/produksi (misal: 150 PCS/Porsi/Box).
  - `orders`: Kuota dihitung berdasarkan total transaksi (`COUNT(commerce_orders.id)`), cocok untuk kapasitas antrean pengantaran.
* **Dynamic Quota Unit Labeling:** Kolom `preorder_quota_unit` (default `PCS`) menyajikan label satuan dinamis pada antarmuka checkout ("Sisa 150 PCS", "Sisa 149 Box", dll.).
* **Mode Batch Rutin vs Kalender Spesifik:** Melalui `batch_dates_mode`:
  - `operating_days`: Otomatis menghasilkan batch mingguan berulang sesuai hari operasional yang aktif (misal: Jumat saja).
  - `custom_dates`: Merchant mendefinisikan daftar tanggal kalender spesifik beserta kuota per-batch melalui kolom JSON `custom_batch_dates`, ideal untuk PO hari raya, seasonal batch, atau catering kustom.
* **Server-Side Guardrail:** `CommerceOrderService::createScheduledOrder()` menolak secara tegas tanggal pesanan di luar batch resmi saat `allow_custom_date = false`, serta memvalidasi ketersediaan sisa kuota PCS/kuantitas sebelum transaksi dicatat.
* **Join Pre-Order & Office Group Buying (Bento Apple HIG):**
  - **Shareable Batch Link:** Merchant atau koordinator kantor dapat menyalin link langsung `/b/{slug}?batch=YYYY-MM-DD` (opsional dengan grup/kantor `&group=NamaKantor`).
  - **Auto Pre-Selection:** Halaman etalase publik langsung mengunci tanggal batch yang diminta dan menampilkan Bento Hub Pre-Order dengan aksi satu klik *Salin Link* & *Share WhatsApp*.
  - **Label Pemesan per Item:** Setiap item di keranjang dapat disematkan nama pemesan / catatan (`item.notes`), mencegah kekeliruan menu antar rekan kerja kantor.
  - **Lokalisasi 100% Bahasa Indonesia:** Hari (`SENIN`, `SELASA`, `RABU`, `KAMIS`, `JUMAT`, `SABTU`, `MINGGU`) dan bulan diformat secara deterministik independen dari setting locale server OS.

---

## 5. Matriks Penelusuran Pengetahuan (Traceability Matrix)

Dokumentasi Cooca saling terhubung secara dua arah untuk memudahkan penelusuran asal-usul keputusan arsitektur:

```
[ LAYER 3: SYSTEM GUIDE (docs/SYSTEM_GUIDE.md) ]
   │
   ├──► Modul Costing & HPP ────────► docs/system/modules/costing.md ────► app/Domain/Costing/
   │                                                                        └──► Work History #001
   │
   ├──► Modul POS Kasir ───────────► docs/system/modules/pos.md ────────► app/Domain/Pos/
   │                                                                        └──► Work History #001
   │
   ├──► Modul Gudang & Inventori ──► docs/system/modules/inventory.md ──► app/Domain/Inventory/
   │                                                                        └──► Work History #001
   │
   ├──► Modul Keuangan & Jurnal ───► docs/system/modules/finance.md ────► app/Domain/Finance/
   │                                                                        └──► Work History #001
   │
   ├──► Modul Toko Storefront ─────► docs/system/modules/commerce.md ───► app/Domain/Commerce/
   │                                                                        └──► WORK-2026-09-15-001
   │
   ├──► Arsitektur Multi-Tenant ───► docs/system/architecture/multi-tenancy.md ──► App\Support\Context
   │                                                                                └──► WORK-2026-09-15-002
   │
   │
   ├──► UI/UX Design System (v2) ──► docs/system/architecture/ui-ux-design-system.md ──► resources/views/layouts/
   │                                                                                             └──► WORK-2026-09-16-010
   │
   ├──► Diagnostik & Error Logs ───► docs/system/modules/system-diagnostics.md ────────► AdminErrorLogController
   │                                                                                            └──► WORK-2026-09-16-014
   │
   ├──► Admin Console & Analytics ─► docs/system/architecture/ui-ux-design-system.md ──► AdminDashboardController
   │                                                                                            └──► WORK-2026-09-16-020
   │
   ├──► Modul Katalog & Produk ─────► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/products/
   │                                                                                            └──► WORK-2026-09-17-044
   │
   ├──► Modul Bahan Baku & Pemasok ──► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/materials/
   │                                                                                            └──► WORK-2026-09-17-045
   │
   ├──► Modul Jasa & Layanan ────────► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/services/
   │                                                                                            └──► WORK-2026-09-17-045
   │
   ├──► Modul Storefront Hub ────────► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/storefront/
   │                                                                                            └──► WORK-2026-09-17-046
   │
   ├──► Modul Landing Page Studio ───► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/landing_page/
   │                                                                                            └──► WORK-2026-09-17-046
   │
   ├──► WhatsApp Cloud API ──────────► docs/system/architecture/multi-tenancy.md ─────────► app/Domain/WhatsApp/CloudApi/
   │                                                                                            └──► WORK-2026-09-17-048
   │
   ├──► Media Sosial (Meta & TikTok) ─► docs/social-media/architecture.md ─────────────────► app/Domain/SocialMedia/
   │                                                                                            └──► WORK-2026-09-17-053
   │
   └──► Unified Settings & SMTP ───► docs/system/architecture/ui-ux-design-system.md ──► AdminSettingController
                                                                                                └──► WORK-2026-09-16-021
```

---
*Dokumen ini merupakan panduan resmi hidup (living guide) sistem Cooca ERP & POS. Setiap pembaruan fungsional atau arsitektur pada source code wajib tercermin dalam System Knowledge Base dan diperbarui di System Guide ini.*
