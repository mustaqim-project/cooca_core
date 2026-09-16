# Modul WhatsApp Gateway & Admin Center (WhatsApp Service)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/WhatsApp/`, `app/Domain/WhatsApp/Drivers/MetaWhatsAppCloudDriver.php`, `wa-server/`, `app/Http/Controllers/Admin/AdminWhatsAppController.php`, `app/Http/Controllers/Web/WhatsApp/WhatsAppWebController.php`  
> **Tabel Basis Data:** `whatsapp_sessions`, `whatsapp_admin_blasts`, `whatsapp_admin_blast_recipients`, `whatsapp_subscription_reminders`, `system_settings`  
> **Route / UI Utama:** `/admin/whatsapp` (Admin WhatsApp Center), `/whatsapp` (Business Owner WhatsApp Gateway)

---

## 1. Tujuan & Nilai Bisnis

Modul WhatsApp Gateway & Admin Center berfungsi sebagai pusat komunikasi otomatis tingkat platform (*Platform-Level Communication Hub*) dan tingkat bisnis operasional (*Store-Level Automation Hub*). Modul ini mengintegrasikan dua penyedia gateway berbeda secara bersamaan:

1. **Meta WhatsApp Cloud API (Graph API v20.0 - Resmi Facebook):**  
   Solusi resmi berbasis cloud serverless dari Meta khusus untuk pesan verifikasi keamanan tingkat tinggi (OTP pendaftaran, login, perubahan nomor telepon) dan notifikasi transaksional tanpa risiko diblokir/banned.
2. **Baileys WA Server (Lokal Scan QR - Multi-Device Client):**  
   Solusi mandiri berbasis web client yang menghubungkan nomor WhatsApp pribadi atau operasional toko via pemindaian kode QR tanpa kartu kredit untuk pesan berkala dan promosi.

---

## 2. Arsitektur Dual Gateway & Pemisahan Jalur (OTP vs Blast)

Untuk mencegah risiko nomor WhatsApp diblokir permanen oleh sistem Meta (*WhatsApp Anti-Ban Architecture*), Cooca memisahkan jalur komunikasi secara independen:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          COOCA WHATSAPP ROUTER                              │
├──────────────────────────────────────┬──────────────────────────────────────┤
│    KANAL OTP & VERIFIKASI KEAMANAN    │    KANAL BROADCAST, PENGINGAT & POS  │
│    (Login, Register, Phone Change)   │    (Subscription Due, Blast, Receipt)│
├──────────────────────────────────────┼──────────────────────────────────────┤
│ ⭐️ META WHATSAPP CLOUD API (Resmi)   │ 📱 BAILEYS WA SERVER (Scan Barcode)  │
│ • Bebas banned 100%                  │ • Tanpa biaya kartu kredit           │
│ • 1.000 percakapan gratis / bulan    │ • Jeda manusiawi acak (3-6 detik)    │
│ • Template pesan resmi Meta          │ • Cooldown otomatis tiap 10 pesan    │
└──────────────────────────────────────┴──────────────────────────────────────┘
```

---

## 3. Fitur & Pengaturan Dual Gateway

### 3.1 Platform Admin (`/admin/whatsapp`)
- **Peringatan Risiko Blokir Sangat Besar (Apple HIG Warning Card):**  
  Menampilkan peringatan mencolok saat Baileys digunakan agar administrator sadar risiko penggunaan nomor personal untuk lonjakan pesan massal.
- **Konfigurasi Kanal OTP & Siaran Terpisah:**  
  Admin wajib memilih jenis gateway untuk masing-masing jalur:
  - **Jalur Kode Masuk (OTP)**: Pilihan antara ⭐️ Meta WhatsApp Cloud API (Resmi & Sangat Stabil) atau 📱 Scan QR Baileys (Server Lokal).
  - **Jalur Pesan Siaran & Pengingat**: Pilihan antara ⭐️ Meta WhatsApp Cloud API (Resmi & Sangat Stabil) atau 📱 Scan QR Baileys (Server Lokal - Bebas Biaya Template).
- **Multi-Session WhatsApp Admin Pool (`whatsapp_admin_sessions`):**  
  Platform admin dapat mendaftarkan banyak nomor WhatsApp sekaligus via Baileys. Setiap nomor memiliki session ID unik, status koneksi, nomor telepon, dan sakelar keikutsertaan pool rotasi acak (`is_active`).
- **Rotasi Acak Otomatis (Load-Balancing Anti-Ban):**  
  Ketika mengirim pesan OTP, notifikasi tagihan langganan, atau siaran pengumuman lewat Baileys, `AdminWhatsAppService::getRandomConnectedSessionId()` memilih nomor pengirim secara acak dari sesi-sesi yang berstatus `connected` dan `is_active = true`. Ini mendistribusikan lalu lintas secara merata ke multi-nomor.
- **Isolasi Direct Meta WhatsApp Cloud API (Non-Random):**  
  Jika admin memilih Meta WhatsApp Cloud API resmi, pengiriman dilakukan secara langsung tanpa di-random ke nomor WABA resmi Cooca.
- **Live QR Scanner & Empty State Ramah Boomer:**  
  Jika belum ada nomor terhubung, UI menampilkan Empty State bersahabat (*"WhatsApp Admin Belum Terhubung"* dengan tombol *"Mulai & Tampilkan Kode QR"*). Saat tombol diklik, modal scanner muncul dengan polling real-time 2.5 detik hingga sesi connected.
- **Form Kredensial Meta WhatsApp Cloud API:**  
  Penyimpanan aman Permanent Access Token, Phone Number ID, WABA ID, dan Template Name dengan enkripsi di tabel `system_settings`.
- **Live Verification Meta Graph API (`POST /admin/whatsapp/verify-meta`):**  
  Admin dapat menguji validitas kredensial token dan ID nomor secara instan sebelum menyimpan ke database.
- **Rute Konfigurasi & Sesi:**  
  `POST /admin/whatsapp/config` (`admin.whatsapp.config`), `GET/POST /admin/whatsapp/sessions` (`admin.whatsapp.sessions.*`).

### 3.2 Business Owner (`/whatsapp`)
- **Pilihan Provider Dinamis:**  
  Pemilik bisnis dapat memilih untuk menyambungkan nomor melalui **Meta WhatsApp Cloud API** (memasukkan Token dan Phone Number ID toko) atau **Baileys WA Server** (memindai kode QR perangkat tertaut).
- **Penyimpanan Non-Destruktif (Atomic Settings):**  
  Pembaruan pengaturan toko dilakukan secara atomik; menyimpan pengaturan struk kasir POS tidak akan menghapus kredensial Meta atau mereset provider yang sedang aktif.
- **Live Verification Kredensial Meta Toko (`POST /whatsapp/verify-meta`):**  
  Pemilik bisnis dapat menguji koneksi akun Meta for Developers mereka dengan 1-klik sebelum menyimpan.
- **Banner Peringatan Tingkat Blokir:**  
  Muncul otomatis saat Baileys dipilih untuk mengingatkan risiko banned jika digunakan untuk blast massal beruntun.
- **Sakelar Status Aktif/Nonaktif:**  
  Pemilik bisnis dapat menonaktifkan atau mengaktifkan kembali layanan WhatsApp toko sewaktu-waktu.
- **Pencegahan Spam & Ban Blast:**  
  Jeda kirim pesan acak 3–5 detik per kontak pelanggan plus istirahat 10 detik setiap 10 pesan untuk gateway Baileys, serta throttling 200ms untuk Meta Cloud API.

### 3.3 Panduan Interaktif Langkah-demi-Langkah Meta Cloud API (`whatsapp-meta-setup-guide.blade.php`)
Tersedia komponen terpadu (*reusable modular partial*) yang diintegrasikan langsung pada UI Platform Admin (`/admin/whatsapp`) dan Business Owner (`/whatsapp`) dengan diferensiasi alur yang disesuaikan secara spesifik:
- **Diferensiasi Peran (Admin vs Owner):**
  - **Mode Platform Admin (`mode='admin'`):**
    - Identitas Pengirim: Akun resmi platform Cooca (`Cooca Gateway`).
    - Tujuan: Pengiriman kode OTP masuk/verifikasi keamanan platform & pesan siaran pengumuman ke pemilik toko.
    - Template Meta (Langkah 6): Kategori **AUTHENTICATION** (`cooca_otp` dengan tombol *Copy Code*) yang disetujui cepat 1–5 menit oleh AI Meta.
  - **Mode Bisnis Owner (`mode='owner'`):**
    - Identitas Pengirim: Nama toko resmi UMKM sendiri (misal: `Kopi Sejahtera POS`).
    - Tujuan: Pengiriman struk belanja digital kasir POS otomatis dan pembaruan status order ke pembeli.
    - Template Meta (Langkah 6): Kategori **UTILITY** (`struk_pembelian`).
    - *Bebas Pusing OTP*: Pemilik toko ditegaskan **tidak perlu membuat template OTP** karena keamanan login dan akun sudah ditangani penuh oleh sistem Cooca.
- **Gaya Apple HIG Stepper:** Menyajikan 6 langkah mudah dipahami dengan bilah navigasi pil (Pill Navigation):
  1. **Langkah 1:** Masuk ke Meta for Developers (`developers.facebook.com`) & Buat Aplikasi (Tipe: *Other* ➔ *Business*).
  2. **Langkah 2:** Tambahkan Produk WhatsApp ke Aplikasi (*Set Up WhatsApp*).
  3. **Langkah 3:** Salin *Phone Number ID* dan *WhatsApp Business Account ID (WABA ID)* dari menu *API Setup*.
  4. **Langkah 4:** Pembuatan *Permanent Access Token (System User Token)* melalui Meta Business Settings (`business.facebook.com/settings/system-users`) agar tidak kedaluwarsa 24 jam, lengkap dengan 1-klik copy untuk perizinan wajib (`whatsapp_business_messaging`, `whatsapp_business_management`).
  5. **Langkah 5:** Menautkan Nomor WhatsApp Resmi (Platform Cooca atau Toko UMKM).
  6. **Langkah 6:** Pendaftaran Template Pesan Resmi (Template OTP untuk Admin atau Template Struk POS untuk Owner).
- **Interaksi Zero-Distraction & Boomer-Friendly:**
  - Dilengkapi fitur *Accordion Toggle* (Buka/Tutup Panduan) sehingga tidak memadati layar jika pengguna sudah mahir.
  - Tautan langsung ke portal Meta yang relevan (`target="_blank"`).
  - Tombol salin 1-klik (*One-Click Clipboard Copy*) untuk parameter teknis guna mencegah salah ketik.
  - Penjelasan kuota gratis 1.000 percakapan per bulan resmi dari Meta tanpa perlu kartu kredit.

---

## 4. Keamanan & Mitigasi Risiko Meta (*Anti-Banned Guardrails*)

1. **Isolasi OTP Terpusat (`sendOtp`):**  
   Seluruh controller autentikasi (`AuthOtpController`, `AuthWebController`, `GoogleAuthController`, `ProfileWebController`, `CustomerOtpController`) wajib memanggil `$adminWa->sendOtp($phone, $otpCode)` sehingga pemilihan driver Meta Cloud API vs Baileys terpusat di satu titik.
2. **Jeda Manusiawi (*Humanized Pacing*):**  
   Pengiriman pesan massal Baileys tidak lagi memakai jeda kaku 1.5 detik, melainkan diacak antara 3 hingga 6 detik, ditambah jeda istirahat 10 detik setiap kelipatan 10 pesan.
3. **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*):**  
   Peringatan risiko blokir dirancang informatif dan solutif dengan opsi mitigasi beralih ke Meta Cloud API.
4. **Apple Bento UI & Human-Friendly Simplicity (`/admin/whatsapp`):**  
   Tata letak mengadopsi Squircle Continuous (`rounded-[20px]`/`rounded-[22px]`/`rounded-[24px]`), white space 8pt grid, bottom clearance `pb-28 lg:pb-12`, atribut dimensi SVG eksplisit (`width="..." height="..."`), skala font mobile minimal 16px (`text-[16px] sm:text-[13px]`) anti auto-zoom, serta microcopy bahasa Indonesia yang santun dan mudah dipahami oleh pengguna usia 40–65+ tahun.
5. **Mockup Balon Chat Realistis (`/admin/whatsapp/blasts/{id}`):**  
   Menyajikan pratinjau pesan broadcast di HP penerima dengan tampilan balon chat WhatsApp hijau khas (`#E7FFDB` / `#005C4B`), centang dua biru, dan penampil gambar banner interaktif.

---

## 5. Verifikasi & Pengujian Otomatis (100% Zero-Error Mandate)

Modul ini diverifikasi melalui test suite otomatis:
- `tests/Feature/Admin/WhatsAppDualGatewayTest.php` (9 tests, 44 assertions, 0 failures)
- `tests/Feature/Admin/AdminWhatsAppFeatureTest.php` (9 tests, 35 assertions, 0 failures)
- `tests/Feature/CustomerWebFeatureTest.php` (5 tests, 25 assertions, 0 failures)
- `tests/Feature/OtpZeroDigitTest.php` & `UserAuthTest.php` (6 tests, 33 assertions, 0 failures)

**Hasil Verifikasi:**  
Seluruh test suite dinyatakan **LOLOS 100% (0 fail, 0 error)**.
