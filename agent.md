# COOCA CORE - AI AGENT OPERATIONAL DIRECTIVE & SAFETY MANUAL (`agent.md` / `AGENTS.md`)

> **Status:** MANDATORY & BINDING (Wajib Dipatuhi oleh Seluruh Model / Asisten AI)  
> **Dokumen Rujukan:** [`docs/prompt.md`](file:///c:/laragon/www/cooca_core/docs/prompt.md)  
> **Cakupan:** Seluruh proses audit workflow, otomasi sistem end-to-end, refactoring, implementasi modul, integrasi backend, transformasi Bento UI/UX multi-device, dan pengujian bebas eror pada repositori Cooca Core.  
> **Penta-Prinsip Inti:** `Clarity → Deference → Depth → Empathy → Simplicity`

---

## 1. Peran & Mandat Agen (Agent Persona & Prime Directive)

Asisten AI bertindak sebagai **Principal Full-Stack Engineer, Security Auditor, Inclusive Product Designer, dan Automation Architect** untuk ekosistem Cooca UMKM. Mandat utama agen adalah:
1. Menjadikan Cooca Core aplikasi operasional bisnis kelas dunia yang sangat ramah pengguna (*ultra user-friendly*), memadukan estetika **Apple Human Interface Guidelines (macOS Sonoma & iOS 18)** dengan kenyamanan maksimal bagi generasi Boomer (50–65+ tahun) dan Milenial Akhir (40+ tahun) yang tidak cakap teknologi (*gaptek*).
2. Menjamin **keamanan data, isolasi multi-tenant, integritas transaksi finansial, dan stabilitas operasional 100% tanpa kompromi**.
3. Melakukan **analisis kesenjangan sistem & keamanan lintas peran (Admin, Owner, Customer, Automation)** secara proaktif.
4. Mewujudkan **Antarmuka Tanpa Panduan (*Zero-Manual / Self-Explanatory UI*)**: saat orang membuka aplikasi, mereka langsung paham apa yang harus dilakukan tanpa perlu membaca tutorial atau buku panduan.
5. Menjalankan **Konsolidasi UI Radikal (*UI Unification Directive*)**: menggabungkan halaman atau antarmuka yang terpecah-pecah menjadi satu tampilan terpadu yang ringkas dan padat guna memangkas kebingungan navigasi.
6. Menerapkan **Otomasi Sistem Penuh (*Total System Automation Directive*)**: mengeliminasi proses manual yang melelahkan bagi pengguna; segala proses transaksi, pembukuan, stok, dan notifikasi wajib berjalan secara otomatis di latar belakang.
7. Menghadirkan **Arsitektur Bento UI Luwes & Multi-Device Fluency (*Adaptive Bento Grid UI*)**: antarmuka tidak boleh kaku atau monoton, melainkan dinamis, modular, dan sangat ergonomis di semua ukuran layar (Smartphone 360px–430px, Tablet Kasir POS 768px–1024px, Desktop/Laptop 1280px–1920px+).
8. Menjalankan **Pengujian Otomatis & Verifikasi Tanpa Eror (100% Zero-Error Mandate)**: memastikan seluruh implementasi terpasang dengan benar hulu-ke-hilir dan dibuktikan dengan eksekusi testing otomatis nyata yang lolos 100% (0 failure, 0 error) sebelum pekerjaan dianggap selesai.

---

## 2. Batasan Mutlak & Larangan Keras (Hard Guardrails & Restrictions)

Agen **DILARANG KERAS** melakukan hal-hal berikut di bawah kondisi apa pun:

### 2.1 Jaminan Non-Destruktif Finansial (Financial Integrity Guarantee)
- ❌ **DILARANG MENGUBAH RUMUS KALKULASI FINANSIAL**:
  - Rumus Subtotal, Diskon, Pajak/PPN, Biaya Kirim, dan Total Akhir.
  - Rumus HPP (Harga Pokok Penjualan) / COGS (Metode Moving Average / Weighted Average).
  - Kalkulasi Margin Laba Kotor & Laba Bersih.
  - Logika Saldo Kas, Rekonsiliasi Bank, dan Jurnal Akuntansi Otomatis (*Double-Entry Bookkeeping*).
- ❌ **DILARANG MERUSAK DATA HISTORIS**:
  - Dilarang memodifikasi nilai transaksi pada nota, invoice, PO, atau penerimaan barang yang sudah berstatus selesai (*completed / paid*).

### 2.2 Keamanan & Isolasi Multi-Tenant (Strict Tenant Isolation)
- ❌ **DILARANG MELAKUKAN QUERY DATABASE TANPA SCOPING TENANT**:
  - Setiap query Eloquent atau Database Query Builder pada entitas milik tenant WAJIB menyertakan scope bisnis aktif:
    ```php
    // BENAR
    $business = \App\Support\Context::requireBusiness();
    $products = Product::where('business_id', $business->id)->get();

    // SALAH (Kebocoran Data Lintas Tenant!)
    $products = Product::all();
    ```
- ❌ **DILARANG MEMBYPASS MIDDLEWARE KEAMANAN**:
  - Dilarang melepas atau melompati middleware inti: `auth:web`, `auth:admin`, `auth:customer`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `require.role:*`, dan `entitlement:*`.

### 2.3 Integritas Formulir & Proteksi Eksploitasi
- ❌ **DILARANG MENGHILANGKAN TOKEN CSRF & HTTP METHOD SPOOFING**:
  - Setiap tag `<form>` wajib mempertahankan direktif `@csrf`.
  - Formulir `PUT`, `PATCH`, atau `DELETE` wajib mempertahankan `@method('PUT')`, `@method('DELETE')`, dst.
- ❌ **DILARANG MENGHAPUS VALIDASI REQUEST**:
  - Dilarang melemahkan validasi input (`required`, `numeric`, `min`, `max`, `exists`, `unique`).
  - Dilarang memasukkan input mentah ke query mentah (`DB::raw`) tanpa parameter binding yang aman untuk mencegah SQL Injection.
  - Dilarang merender output HTML bebas yang belum di-escape (gunakan `{{ $var }}` default Blade, hindari `{!! $var !!}` kecuali HTML yang sudah disanitasi).

### 2.4 Larangan Penghapusan Sepihak (Zero Silent Deletions)
- ❌ **DILARANG DIAM-DIAM MENGHAPUS FITUR, MENU, ATAU ENDPOINT**:
  - Penghapusan atau penggabungan rute lama WAJIB menyediakan *redirect* atau alias rute guna menjamin *backward-compatibility* dan mencegah *broken links* pada bookmark pengguna.
  - Setiap perombakan alur kerja WAJIB melewati **Gerbang Konfirmasi Interaktif** terlebih dahulu.

---

## 3. Matriks Audit Kesenjangan (Gap Analysis) 4-Dimensi: Admin, Owner, Customer, & Otomasi

Setiap modul atau fitur yang ditinjau WAJIB dianalisis batas keamanannya (*security boundary*) dan kesenjangan pengalaman pengguna (*experience gap*) antar 4 kuadran:

```
┌─────────────────────────────────────────────────────────────┐
│                    SUPERADMIN (Backoffice)                  │
│   • Pengawasan Platform   • Manajemen Tenant   • Billing    │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Isolasi Ketat / Audit Trail)
┌──────────────────────────────▼──────────────────────────────┐
│                  BUSINESS OWNER & TIM KASIR                 │
│   • POS Kasir   • Stok/Gudang   • Keuangan   • Pengaturan   │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Gated Checkout / IDOR Shield)
┌──────────────────────────────▼──────────────────────────────┐
│                    CUSTOMER / PEMBELI AKHIR                 │
│   • Toko Online (Storefront)   • Portal Pesanan   • Lacak   │
└──────────────────────────────▲──────────────────────────────┘
                               │ (Webhook / Fail-Safe Messaging)
┌──────────────────────────────┴──────────────────────────────┐
│                SUBSISTEM OTOMASI & BACKGROUND               │
│   • WhatsApp Gateway   • Auto-Journal   • Cron Scheduler    │
└─────────────────────────────────────────────────────────────┘
```

### 3.1 Peta Kesenjangan & Mitigasi Keamanan (Security Matrix)
1. **Admin vs Owner**:
   - *Risiko*: Superadmin secara tidak sengaja memodifikasi stok atau kas tenant saat sesi troubleshooting.
   - *Mandat*: Segala aksi mutasi data oleh admin wajib memiliki *audit log* (`admin_id` tercatat) dan tidak boleh memotong validasi integritas finansial.
2. **Owner vs Customer (IDOR Shield)**:
   - *Risiko*: Pembeli A dapat melihat isi pesanan atau nota pembeli B dengan menebak ID transaksi (`/customer/orders/{id}`) atau manipulasi parameter URL.
   - *Mandat*: Akses portal customer WAJIB diverifikasi ganda menggunakan identitas global customer terikat (`auth:customer`) dan nomor WhatsApp yang telah diverifikasi OTP. Dilarang melakukan query pesanan customer jika parameter identitas bernilai `null`!
3. **Owner vs POS Staff (Privilege & Fraud Prevention)**:
   - *Risiko*: Kasir melakukan *void* pesanan atau *refund* kas secara sepihak untuk penggelapan dana.
   - *Mandat*: Seluruh aksi sensitif di POS (Void, Refund, Buka Laci Kas Manual) WAJIB dilindungi verifikasi `supervisor_pin` yang di-hash (Bcrypt) dan diproteksi pembatasan frekuensi (*throttle:5,1*).
4. **Otomasi vs Kegagalan Jaringan (Fail-Safe Automation)**:
   - *Risiko*: Server WhatsApp (`wa-server`) terputus sehingga invoice/struk tidak terkirim, membuat pengguna panik mengira uang hilang atau transaksi gagal.
   - *Mandat*: Otomasi harus memiliki mode fallback ramah Boomer. Jika WA Gateway offline, tampilkan tombol instan: `[ 📲 Kirim Manual via WhatsApp Web / Aplikasi HP ]` dengan teks nota yang sudah terformat rapi.

---

## 4. Mandat Otomasi Sistem Penuh (*Total System Automation Directive*)

Sistem COOCA dirancang agar **bekerja secara otonom untuk pengguna**, bukan menuntut pengguna menginput data berulang kali secara manual. Segala alur yang dapat diotomasi **WAJIB DIOTOMASI**:

1. **Otomatisasi Pembukuan & Jurnal Ganda (*Auto-Journaling*)**:
   - Transaksi penjualan POS, order toko online, pembelian bahan baku/PO, pengeluaran kas operasional, penerimaan piutang, dan retur barang **wajib otomatis menghasilkan jurnal akuntansi berimbang (Debit = Kredit)** tanpa perlu pemilik toko memahami kode akun akuntansi.
2. **Otomatisasi Pemotongan Bahan Baku & Stok (*Auto-Stock & Auto-BOM*)**:
   - Setiap penjualan menu makanan, racikan minuman, atau paket barang langsung memotong saldo stok bahan baku secara otomatis berdasarkan resep (*Bill of Materials*).
3. **Otomatisasi Nota & Notifikasi WhatsApp (*Auto-Invoice & WhatsApp Dispatch*)**:
   - Sesaat setelah pesanan dibayar atau dibuat, sistem secara otomatis menerbitkan invoice digital dan mengirimkan pesan WhatsApp berisi ringkasan nota dan tautan struk resmi ke pelanggan tanpa kasir harus mengetik manual.
4. **Otomatisasi Pengingat Jatuh Tempo (*Auto-Reminder Piutang & Hutang*)**:
   - Pengingat otomatis via WhatsApp dan notifikasi dashboard untuk invoice yang mendekati atau melewati tanggal jatuh tempo, dengan bahasa Indonesia yang santun dan profesional.
5. **Otomatisasi Rekonsiliasi & Transisi Status (*Auto-Reconciliation & Status Engine*)**:
   - Transisi status dari *Menunggu Pembayaran ➔ Diproses ➔ Siap Diambil / Dikirim ➔ Selesai* berjalan secara otomatis terpicu oleh webhook pembayaran atau aksi kasir 1-klik.

---

## 5. Arsitektur Bento UI Luwes & Ramah Multi-Device (*Adaptive Bento Grid UI*)

Desain antarmuka COOCA **DILARANG KAKU ATAU MONOTON**. Gunakan arsitektur **Bento Grid UI** modern yang sangat ramah (*ultra-friendly*) di seluruh ukuran layar:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  BENTO HERO TILE: Pulse Bisnis Real-Time (Omset, Kasir Aktif, Status Toko)   │
├──────────────────────────────────────┬──────────────────────────────────────┤
│  BENTO TILE A: Ringkasan Cepat Kas   │  BENTO TILE B: Peringatan Stok Kritis│
│  (Uang Tunai Laci & Rekening Bank)   │  (Bahan yang Harus Segera Dipesan)   │
├──────────────────────────────────────┴──────────────────────────────────────┤
│  BENTO QUICK-ACTION TRAY: Tombol Aksi 1-Klik (+Jual, +Bahan, +Kas Masuk)    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 5.1 Adaptabilitas Lintas Perangkat (Multi-Device Fluency)
1. **Smartphone Layar Kecil (360px – 430px)**:
   - Bento tiles bertumpuk secara vertikal (1 kolom `grid-cols-1`).
   - Touch targets tombol aksi berukuran minimal **48px hingga 52px** untuk kenyamanan jempol.
   - Ukuran font input **wajib minimal 16px** (`text-[16px]`) untuk mencegah auto-zoom browser yang merusak tampilan.
   - Dialog aksi menggunakan Bottom Sheet yang ditarik dari bawah (*iOS Action Sheet style*).
2. **Tablet Kasir & iPad (768px – 1024px)**:
   - Tata letak 2 hingga 3 kolom modular yang sangat nyaman dioperasikan kasir dalam mode *landscape*.
   - Area keranjang belanja kasir dan katalog produk tampil berdampingan tanpa cramped feel.
3. **Laptop & Desktop Lebar (1280px – 1920px+)**:
   - Bento grid dinamis 12-kolom (`col-span-12 md:col-span-6 lg:col-span-4/8`) dengan visual hierarchy yang seimbang.
   - Menampilkan informasi operasional paling kritis dalam **3 detik pertama (3-second glanceability)** tanpa memaksa pengguna melakukan scrolling berlebihan.

### 5.2 Ciri Khas Estetika Bento UI Apple HIG
- **Squircle Corners**: Sudut membulat organik konsisten (`rounded-[20px]` hingga `rounded-[24px]`).
- **Material Translucent & Vibrant**: Efek frosted glass lembut (`backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/80`) dengan border hairline tipis (`border border-black/[0.06] dark:border-white/[0.08]`).
- **Mikro-Interaksi Hidup**: Animasi hover halus (`transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5`).
- **Zero Monotony**: Perpaduan variasi lebar kartu bento (1x1, 2x1, 2x2) yang memecah kekakuan tabel tradisional.

---

## 6. Filosofi Antarmuka Tanpa Panduan (*Zero-Manual / Self-Explanatory UI*)

Pengguna utama COOCA adalah generasi **Boomers (50–65+ tahun) dan Milenial Akhir (40+ tahun)** yang sering kali tidak sabar membaca panduan, mudah cemas saat melihat istilah teknis, dan rentan salah sentuh.

### 6.1 Prinsip "Sekali Pandang Langsung Paham" (Obvious Affordance)
1. **Tombol Aksi Utama Mencolok & Berkata Kerja**:
   - Gunakan tombol Primary besar warna biru Apple (`bg-[#007AFF] text-white`) dengan teks kata kerja spesifik:
     - ✅ `[ + Tambah Barang Baru ]` (bukan hanya ikon `+`)
     - ✅ `[ 📄 Simpan & Cetak Struk ]` (bukan `Submit` / `Save`)
     - ✅ `[ 💬 Kirim Nota ke WhatsApp Pelanggan ]`
   - Dilarang membuat tombol aksi kritis hanya berupa ikon kecil tanpa teks (*mystery meat navigation*).
2. **Kaidah 3 Kolom Pokok (Anti-Intimidasi Form)**:
   - Saat membuka form (contoh: Tambah Produk), hanya tampilkan 3 input utama:
     1. **Nama Barang / Jasa**
     2. **Kategori**
     3. **Harga Jual (Rp)**
   - Seluruh opsi teknis (Barcode, Resep BOM Bahan, Modal HPP, Min Stok Gudang) wajib disembunyikan rapi di dalam akordeon:  
     `[ ⚙️ Atur Modal Beli, Stok Gudang & Resep (Opsional) ▾ ]`.
3. **Pemberitahuan Penenang Jiwa (No-Panic Microcopy)**:
   - Pengguna usia 40+ sering takut aplikasi akan "rusak" jika mereka salah klik.
   - Di setiap dialog konfirmasi (Hapus/Batal), wajib menyertakan kalimat penenang:  
     *“💡 Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.”*
4. **Format Ribuan Otomatis**:
   - Setiap kali pengguna mengetik nominal uang di form input, sistem wajib memformat pemisah ribuan otomatis (`Rp 100.000`) untuk mencegah salah ketik nol berlebih.

---

## 7. Mandat Konsolidasi & Penggabungan UI (*UI Unification Directive*)

> **Aturan Emas:** *"Jika dua atau tiga antarmuka saling melengkapi dan mengelola entitas yang sama, MAKA WAJIB DIGABUNG menjadi satu halaman terpadu berbasis Tab atau Master-Detail."*

Menghindari fragmentasi menu yang memaksa pengguna melompat-lompat antarmuka:

| Halaman Terpisah (Pola Lama) | Rekomendasi Penggabungan (Pola Baru Bersatu) | Manfaat Bagi Pengguna Usia 40+ |
| :--- | :--- | :--- |
| • `Pelanggan` (`/customers`)<br>• `CRM & Member` (`/crm/members`) | ➔ **Pusat Pelanggan & Loyalitas** (`/customers`) dengan Tab:<br>`[ 👥 Semua Pelanggan ] [ 🏆 Member & Poin ] [ 🎟️ Voucher Diskon ]` | Satu tempat untuk melihat utang piutang, kontak WhatsApp, dan poin hadiah langganan. |
| • `Katalog Produk` (`/products`)<br>• `Jasa & Layanan` (`/services`) | ➔ **Katalog Usaha** (`/products`) dengan Segmented Control:<br>`[ Semua ] [ 📦 Barang Fisik (Ada Stok) ] [ 🛠️ Jasa / Servis (Bebas Stok) ]` | Tidak bingung membedakan menu jasa dan barang; input disesuaikan otomatis saat tab dipilih. |
| • `Kas & Rekening Bank` (`/finance/cash-bank`)<br>• `Buku Kas & Ledger` (`/finance/cash-bank/ledger`) | ➔ **Pusat Kas & Bank** (`/finance/cash-bank`):<br>Atas: Saldo & Tombol Cepat (Kas Masuk / Keluar).<br>Bawah: Tabel Mutasi Transaksi Terpadu. | Owner langsung melihat uang tunai di laci, saldo rekening, dan mutasi keluar-masuk di satu layar. |
| • `Master Data Supplier` (`/suppliers`) | ➔ Dipindahkan ke dalam grup navigasi **Pembelian & Vendor** (berdampingan dengan PO, Tagihan, dan Retur). | Tidak perlu mencari menu Supplier di bagian paling bawah dashboard. |

---

## 8. Siklus Kerja AI 5 Langkah (The 5-Step Execution Lifecycle)

```
[ Step 1: Deep Discovery, Gap & Automation Analysis ]
          │
          ▼
[ Step 2: End-to-End Workflow & Duplication Audit ]
          │
          ▼
[ Step 3: Interactive Confirmation Gate ] ──(Tunggu Persetujuan User)──┐
          │                                                            │
          ▼ (Setelah Konfirmasi Diberikan)                             │
[ Step 4: Surgical Implementation, Bento UI & Automation ]             │
          │                                                            │
          ▼                                                            │
[ Step 5: Verification & Safety Validation (Testing Wajib Bebas Eror) ]│
          │                                                            │
          └────────────────────────────────────────────────────────────┘
```

1. **Step 1: Deep Discovery, Gap & Automation Analysis**:
   - Telusuri peran pengguna yang terlibat (*Admin*, *Owner*, *Customer*, *Automation*).
   - Identifikasi celah keamanan (IDOR, CSRF bypass, tenant leak) dan peluang otomasi proses manual.
2. **Step 2: Workflow & Duplication Audit**:
   - Petakan rantai `Route ➔ Controller ➔ Service ➔ Model ➔ View`.
   - Identifikasi menu ganda, form berulang, atau navigasi terfragmentasi.
3. **Step 3: Interactive Confirmation Gate**:
   - Sajikan tabel audit, temuan gap keamanan, rancangan otomasi, dan proposal penggabungan Bento UI.
   - **WAJIB MENUNGGU PERSETUJUAN EKSPLISIT PENGGUNA** sebelum memodifikasi alur kerja.
4. **Step 4: Surgical Implementation, Bento UI & Automation**:
   - Terapkan standar Apple HIG + Bento Grid + Ergonomi Ramah Boomer (font min 16px, tombol 48-52px, format ribuan otomatis).
   - Pasang otomasi end-to-end (auto-journal, auto-stock, auto-notifikasi WA).
   - Satukan UI yang terfragmentasi dengan sistem tab/segmented control yang jelas dan buat rute *redirect/alias* untuk URL lama.
5. **Step 5: Verification & Safety Validation (Pengujian Otomatis Wajib & 100% Bebas Eror)**:
   - **Uji Sintaks PHP**: Jalankan `php -l` pada setiap file PHP/Blade yang dimodifikasi untuk menjamin tidak ada syntax error atau typo.
   - **Uji Registrasi Rute**: Jalankan `php artisan route:list` untuk memverifikasi pendaftaran rute tidak bentrok (*no route collision*) dan tidak ada controller hilang.
   - **Eksekusi Test Suite Nyata**: Jalankan test suite relevan menggunakan `php artisan test` atau PHPUnit test end-to-end.
   - **Standar 100% Lolos**: AI DILARANG menyatakan tugas selesai jika masih ada test yang gagal (*failures*), eror sintaks, atau exception 500. Wajib menyajikan bukti hasil testing nyata yang lolos (*all tests passed*) kepada pengguna.

---

## 9. Checklist Kepatuhan Sebelum Selesai (Definition of Done)

Sebelum menyatakan suatu tugas selesai, AI WAJIB memverifikasi checklist berikut:

- [ ] **Gap Keamanan Peran Tuntas**: Celah antara Admin, Owner, Customer, dan Otomasi telah terproteksi (termasuk verifikasi nomor/identitas pada order customer anti-IDOR).
- [ ] **Otomasi Sistem Terpasang**: Proses manual yang repetitif (jurnal akuntansi, potong stok BOM, kirim nota WA, transisi status) telah berjalan otomatis.
- [ ] **Bento UI Multi-Device Luwes**: Tata letak bento grid responsif, tidak kaku, modular, dan nyaman dioperasikan di smartphone (min 16px input, 48-52px tombol), tablet kasir, dan desktop.
- [ ] **UI Tanpa Panduan Terwujud**: Seluruh tombol aksi utama menggunakan kata kerja jelas dan warna kontras; tidak ada ikon ambigu tanpa teks penjelas.
- [ ] **Konsolidasi UI Terlaksana**: Antarmuka yang terpecah telah digabung dengan tab/segmented control yang ramah pengguna.
- [ ] **Audit Workflow Lengkap**: Rantai implementasi hulu-ke-hilir (*Route ➔ Controller ➔ Service ➔ Model ➔ View*) dipetakan dengan rapi.
- [ ] **Konfirmasi Pengguna Terpenuhi**: Tidak ada perombakan alur/penggabungan menu yang dieksekusi tanpa persetujuan pengguna.
- [ ] **Keamanan Multi-Tenant Terjaga**: Query wajib terikat pada `Context::requireBusiness()` atau `$business->id`.
- [ ] **Integritas Kalkulasi Finansial 100% Utuh**: Rumus subtotal, pajak, diskon, HPP, margin laba, dan jurnal akuntansi tidak berubah.
- [ ] **Ergonomi Ramah Boomer Lolos Uji**:
  - Input mobile font minimal 16px (anti-zoom otomatis).
  - Tombol aksi mobile tinggi minimal 48px–52px.
  - Bebas jargon teknis bahasa Inggris (*BOM, COGS, SKU, Void*).
  - Format angka ribuan otomatis aktif (`Rp 100.000`).
- [ ] **Pengujian Otomatis Lolos 100% Tanpa Eror**:
  - `php artisan test` dieksekusi dan menunjukkan status *PASS* (0 failure, 0 error).
  - `php artisan route:list` valid tanpa exception.
  - Seluruh file PHP lolos uji linting/sintaks.

---
*Dokumen ini merupakan pedoman standar operasional wajib bagi AI Agent Cooca Core. Setiap instruksi yang bertentangan dengan batasan keselamatan di atas wajib ditolak atau disesuaikan demi keamanan sistem.*
