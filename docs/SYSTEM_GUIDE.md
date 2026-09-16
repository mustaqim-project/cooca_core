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
4. [Panduan Rekayasa Developer & AI Agent (Engineering Blueprint)](#4-panduan-rekayasa-developer--ai-agent-engineering-blueprint)
   - [4.1 Struktur 33 Domain Packages DDD](#41-struktur-33-domain-packages-ddd)
   - [4.2 Aturan Scoping Tenant & Proteksi Keamanan](#42-aturan-scoping-tenant--proteksi-keamanan)
   - [4.3 Mesin Otomasi Latar Belakang (Auto-Journal & Auto-Stock)](#43-mesin-otomasi-latar-belakang-auto-journal--auto-stock)
   - [4.4 Protokol Verifikasi & Kesiapan Produksi (100% Zero-Error Mandate)](#44-protokol-verifikasi--kesiapan-produksi-100-zero-error-mandate)
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
* **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*):** Di setiap aksi penting, sistem selalu menyertakan pesan penenang:  
  *“💡 Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.”*

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
* **Kapan Digunakan?** Setiap saat Anda ingin melihat posisi kesehatan uang toko (Laba Bersih, Uang di Kasir, Saldo di Bank, dan Piutang Pembeli).
* **Keunggulan Cooca:** Anda **tidak perlu mengerti debit, kredit, atau kode akun**. Sistem secara otonom menjalankan `AutoJournalService` setiap kali transaksi kasir atau pembayaran hutang terjadi.

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
   └──► Unified Settings & SMTP ───► docs/system/architecture/ui-ux-design-system.md ──► AdminSettingController
                                                                                                └──► WORK-2026-09-16-021
```

---
*Dokumen ini merupakan panduan resmi hidup (living guide) sistem Cooca ERP & POS. Setiap pembaruan fungsional atau arsitektur pada source code wajib tercermin dalam System Knowledge Base dan diperbarui di System Guide ini.*


