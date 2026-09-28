# PRD-12: Hardening Keamanan Cyber, Proteksi Fraud Internal & Panduan Sadar Konteks 20 Sektor Industri pada Modul Media Sosial Omnichannel

> **ID Dokumen:** `PRD-12-SOCIAL-MEDIA-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY`  
> **Status Dokumen:** DRAFT FOR CONFIRMATION (Menunggu Persetujuan Pengguna)  
> **Domain Terkait:** `app/Domain/SocialMedia/`, `app/Domain/Storage/`, `app/Domain/Billing/`  
> **Target Pengguna:** Business Owner (Pemilik Toko UMKM), Social Media Marketing Staff, Store Manager, Super Admin Cooca  
> **Standar Arsitektur:** `docs/agent.md`, `docs/SYSTEM_GUIDE.md`, Bento Apple HIG v2.0, Zero .env, Zero Plaintext Credential Exposure

---

## 1. Latar Belakang & Pernyataan Masalah (Problem Statement)

Modul **Media Sosial Omnichannel (`resources/views/app/social_media`)** mengintegrasikan 5 jaringan media sosial papan atas (Facebook, Instagram, Threads, TikTok, LinkedIn) untuk kebutuhan promosi dan keterlibatan pelanggan toko UMKM.

Berdasarkan audit menyeluruh terhadap 5 view Blade (`index.blade.php`, `posts.blade.php`, `calendar.blade.php`, `inbox.blade.php`, `insights.blade.php`) dan alur controller terkait (`SocialMediaWebController`), ditemukan beberapa kelemahan arsitektur, celah keamanan, dan risiko operasional:

1. **Permission Coupling Defect (Pelanggaran Prinsip Least Privilege):**
   - Route group `social-media.*` di `routes/owner.php` dikawal oleh middleware `require.permission:whatsapp.view` alih-alih permission terisolasi untuk media sosial. Hal ini menyebabkan staf yang hanya memiliki izin WhatsApp dapat mengelola media sosial, dan sebaliknya staf pemasaran media sosial tidak dapat mengakses modul tanpa hak akses WhatsApp.
2. **Potensi Server-Side Request Forgery (SSRF) pada URL Media:**
   - Parameter `media_url` pada komposer pos belum divalidasi terhadap resolusi IP intranet/privat (RFC 1918) dan cloud metadata service (`169.254.169.254`).
3. **Kerentanan DOM XSS & Unescaped Data String pada Kotak Masuk:**
   - Di `inbox.blade.php`, data nama pengirim dan pesan komentar dilewatkan ke fungsi Javascript inline menggunakan fungsi `addslashes()` PHP mentah di dalam atribut tag HTML `@click`. Hal ini rentan terhadap injeksi pemutus string serta rusaknya rendering saat komentar mengandung unicode karakter khusus.
   - Pembaruan lencana status masih menggunakan `badge.innerHTML` tidak aman.
4. **Ketiadaan Rate Limiting pada Endpoint Sinkronisasi & Balas Komentar:**
   - Endpoint `POST /social-media/insights/{post}/sync` dan `POST /social-media/comments/{comment}/reply` belum dilengkapi rate limiting (throttle), berisiko memicu pemblokiran kuota Meta Graph API toko.
5. **Skema Fraud Internal & Sabotase Akun (Rogue Staff Risk):**
   - Belum ada mekanisme kendali ganda (*Maker-Checker*). Kasir atau staf toko yang memiliki akses POS/perangkat toko dapat mempublikasikan konten liar atau merusak reputasi toko secara sepihak.
   - Belum ada pendeteksi pencantuman nomor rekening pribadi staf (*phishing/fake bank injection*) di dalam caption pos.
   - Pos induk tidak mencatat `user_id` pembuat asli, sehingga audit forensik sulit dilakukan jika terjadi insiden.
6. **Pelanggaran Standar Desain Bento Apple HIG v2.0:**
   - Modal reply komentar di `inbox.blade.php` masih berukuran sempit `max-w-lg` (melanggar mandat *Modal-First XXL*).
   - Penggunaan dialog native peramban `alert()` dan `confirm()` yang memblokir thread UI dan merusak estetika antarmuka.
   - Ketiadaan deteksi dini asimetri aspek rasio video (16:9 landscape vs 9:16 vertikal untuk Reels/TikTok).
7. **Ketiadaan Guardrail Sektor Industri Bisnis:**
   - Belum adanya penegakan larangan promosi obat keras bagi sektor Apotek/Farmasi (ancaman sanksi BPOM RI & Meta Health Policy).
   - Belum adanya penyamaran plat nomor kendaraan (`nopol`) bagi bengkel otomotif dan privasi data pelanggan salon.

---

## 2. Sasaran Produk & Metrik Keberhasilan (Objectives & Key Results)

| Sasaran | Metrik Keberhasilan | Target |
| :--- | :--- | :---: |
| **Keamanan Cyber** | Eliminasi SSRF, sanitasi DOM XSS via `@js()`, dan throttle rate limiting | 100% Bebas Celah |
| **Isolasi RBAC** | Pemisahan izin resmi `social_media.view` & `social_media.manage` | Terisolasi Sempurna |
| **Proteksi Fraud** | Maker-Checker untuk staf toko & deteksi nomor rekening liar di caption | 0 Konten Liar Terbit |
| **Kepatuhan Apple HIG** | Konversi modal sheet ke XXL (`max-w-5xl`/`xl:max-w-6xl`), eliminasi native `alert()` | 100% Bento HIG |
| **Sadar Konteks Industri** | Penegakan otomatis aturan Do's & Don'ts untuk 20 sektor bisnis | Aktif 20 Sektor |
| **Kesiapan Testing** | Eksekusi seluruh feature test suite media sosial | 100% Lolos (0 Failure) |

---

## 3. Spesifikasi Fungsional (Functional Requirements)

### 3.1 Pemisahan Hak Akses & Middleware RBAC Khusus
- Menghapus middleware `require.permission:whatsapp.view` pada rute `/social-media`.
- Memperkenalkan izin baru di database seeder RBAC:
  - `social_media.view`: Mengakses antarmuka koneksi akun, daftar postingan, kalender konten, dan analitik.
  - `social_media.manage`: Menghubungkan/memutuskan akun pihak ketiga, mempublikasikan postingan baru, menyetujui draf konten, dan membalas komentar pelanggan.

### 3.2 Hardening Keamanan Cyber (SSRF, DOM XSS & Throttle)
- **Anti-SSRF Media URL:** Validasi ketat bahwa `media_url` wajib menggunakan protokol `https://`, memvalidasi alamat IP tujuan via DNS check, dan menolak seluruh IP privat (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `127.0.0.1`, `169.254.169.254`).
- **DOM XSS Elimination:** Mengubah pemanggilan `prepareReply()` di `inbox.blade.php` agar menggunakan helper Blade `@js()` dan data attributes, serta mengganti `.innerHTML` dengan manipulasi DOM yang aman.
- **Throttling Gateway:**
  - `POST /social-media/insights/{post}/sync`: Dibatasi `throttle:10,1` (maksimal 10 request per menit per user).
  - `POST /social-media/comments/{comment}/reply`: Dibatasi `throttle:15,1`.
- **Penggantian Dialog Native Browser:** Mengganti seluruh fungsi `alert()` dan `confirm()` native dengan komponen Cooca `AppAlert.toast()` dan `AppAlert.confirm()`.

### 3.3 Skema Fraud Guard & Maker-Checker Publikasi
- **Struktur Kepemilikan & Status Persetujuan Konten:**
  - Menambahkan kolom pada tabel `social_media_posts`:
    - `user_id`: UUID staf pembuat pos (dicatat otomatis dari `Auth::id()`).
    - `approval_status`: `approved` (otomatis jika dibuat oleh Owner/Manager) atau `pending_review` (jika dibuat oleh Staf/Kasir).
    - `reviewed_by`: UUID pengguna yang menyetujui.
    - `reviewed_at`: Timestamp persetujuan.
  - Jika dibuat oleh staf toko, pos berstatus `pending_review`, tidak langsung didispatch ke antrean API, dan memunculkan tombol *[Setujui & Publikasikan]* khusus bagi Owner/Manager.
- **Pendeteksi Rekening Liar di Caption (Anti-Phishing Caption Scanner):**
  - Heuristik regex memindai teks caption mencari pola nomor rekening bank (BCA, Mandiri, BRI, BNI) atau e-wallet.
  - Jika nomor rekening ditemukan dan tidak terdaftar pada tabel `bank_accounts` tenant aktif, sistem memicu flag `suspicious_bank_account` dan memblokir publikasi langsung hingga diverifikasi Owner.
- **Audit Logging Forensik:**
  - Mencatat aksi pembuatan, persetujuan, penolakan, retry target, dan penghapusan pos ke tabel `audit_logs` lengkap dengan snapshot payload dan IP address.

### 3.4 Peningkatan Antarmuka Bento Apple HIG v2.0 & Human Error Shields
- **Pemberitahuan Jam Senyap (Quiet Hours Shield):**
  - Komposer pos menampilkan banner informasi lembut berwarna oranye jika waktu publikasi dijadwalkan antara pukul 22:00 s/d 06:00 WIB.
- **Pemeriksa Aspek Rasio Video Cerdas:**
  - Skrip Alpine.js membaca metadata resolusi video saat dipilih. Jika format yang dipilih adalah Reels atau TikTok dan video terdeteksi bertipe landscape (lebar > tinggi), muncul notifikasi panduan: *"Video berformat landscape (16:9) terdeteksi. Disarankan menggunakan format vertikal (9:16) untuk hasil maksimal di TikTok/Reels."*
- **Penghitung Karakter Dinamis per Saluran Terpilih:**
  - Karakter counter menyesuaikan batas terkecil dari saluran yang dipilih (contoh: jika Threads dipilih, batas maksimum ditandai 500 karakter).
- **Perluasan Ukuran Modal Sheet (Bento XXL):**
  - Modal komposer pos di `posts.blade.php` diperluas dari `max-w-3xl` menjadi `max-w-5xl xl:max-w-6xl` dengan layout 2 kolom (formulir di sisi kiri, pratinjau media di sisi kanan).
  - Modal reply di `inbox.blade.php` diubah menjadi modal responsif berstandar Apple HIG dengan feedback visual terpadu.

### 3.5 Penegakan Aturan Kontekstual 20 Sektor Industri
- **Apotek & Toko Obat (`retail_pharmacy`):**
  - Banner peringatan merah permanen di komposer pos: *"PERINGATAN REGULASI FARMASI: Dilarang mempublikasikan obat keras (Daftar G), antibiotik, dan obat resep dokter di media sosial sesuai ketentuan BPOM RI & Meta Health Policy."*
- **Bengkel & Otomotif (`service_workshop`, `service_autodetailing`):**
  - Catatan privasi: *"Pastikan plat nomor kendaraan (nopol) pelanggan disensor/diburamkan sebelum mengunggah foto demi menjaga privasi data pelanggan."*
- **Salon & Barbershop (`service_barbershop`):**
  - Pengingat izin: *"Pastikan telah memperoleh izin pelanggan sebelum mempublikasikan foto transformasi gaya rambut/perawatan."*
- **Manufaktur & Konveksi (`mfg_*`):**
  - Pengingat kerahasiaan: *"Pastikan desain pesanan maklon tidak melanggar perjanjian kerahasiaan hak cipta klien."*

---

## 4. Rencana Perubahan Skema Basis Data (Database Changes)

Migrasi database baru: `2026_09_28_100000_add_audit_and_approval_to_social_media_posts.php`
- Menambahkan pada tabel `social_media_posts`:
  - `user_id` (char 36, nullable, indexed, foreign key ke `users.id`)
  - `approval_status` (enum: `approved`, `pending_review`, `rejected`, default: `approved`)
  - `reviewed_by` (char 36, nullable, foreign key ke `users.id`)
  - `reviewed_at` (timestamp, nullable)
  - `rejection_reason` (text, nullable)
  - `risk_flags` (json, nullable)

---

## 5. Matriks Risiko & Mitigasi Teknis

| Risiko Teknis / Regresi | Probabilitas | Dampak | Mitigasi yang Diterapkan |
| :--- | :---: | :---: | :--- |
| **Breaking Existing Post Creation** | Rendah | Tinggi | Nilai default `approval_status = 'approved'` untuk Owner/Manager sehingga workflow eksisting tidak terganggu |
| **Penolakan Validasi Media URL yang Sah** | Rendah | Sedang | Hanya menolak IP privat (RFC 1918) dan scheme selain `https://`; domain publik HTTPS tetap lolos 100% |
| **False-Positive Deteksi Rekening Bank** | Sedang | Rendah | Mengizinkan Owner untuk melakukan bypass otorisasi secara sadar (*Override Warning*) |
| **Perubahan Tampilan di Layar Ponsel** | Rendah | Sedang | Modal tetap mempertahankan native Apple bottom sheet (`rounded-t-[28px]`, grab handle) pada layar mobile |
