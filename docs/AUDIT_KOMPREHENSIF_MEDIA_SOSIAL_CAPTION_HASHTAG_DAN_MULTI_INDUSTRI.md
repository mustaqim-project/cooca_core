# AUDIT KOMPREHENSIF SISTEM & ARSITEKTUR
## Modul Media Sosial Omnichannel COOCA (`resources/views/app/social_media`)

> **ID Dokumen Audit:** `AUDIT-COOCA-SOCMED-CAPTION-HASHTAG-MULTI-INDUSTRY-01`  
> **Tanggal Audit:** 2026-09-29  
> **Status:** AUDITED & DOKUMENTASI TERVERIFIKASI  
> **Auditor:** Principal Full-Stack Engineer, Security Auditor & Laravel Architect  
> **Ruang Lingkup Berkas:**  
> - 5 View Blade: `resources/views/app/social_media/index.blade.php`, `posts.blade.php`, `calendar.blade.php`, `inbox.blade.php`, `insights.blade.php`  
> - Controller & Rute: `app/Http/Controllers/Web/SocialMedia/SocialMediaWebController.php`, `routes/owner.php`  
> - Domain & Validation: `app/Domain/SocialMedia/Validation/SocialMediaContentValidator.php`, `app/Domain/SocialMedia/SocialMediaService.php`, `app/Domain/SocialMedia/SocialMediaManager.php`  
> - Providers: `MetaProvider.php`, `TikTokProvider.php`, `LinkedInProvider.php`  
> - Lokalisasi i18n: `lang/id/social_media.php`, `lang/en/social_media.php`  
> - Test Suite: `tests/Feature/SocialMedia/*`

---

## 1. Eksekutif Ringkasan (Executive Summary)

Modul **Media Sosial Omnichannel COOCA** merupakan komponen strategis dalam *Hub Pelanggan & Kanal Digital* yang memungkinkan ribuan pelaku UMKM Indonesia mengelola, menerbitkan, menjadwalkan, membalas komentar, dan menganalisis metrik dari 5 jejaring media sosial papan atas (Meta Facebook Page, Instagram Bisnis, Meta Threads, TikTok for Business, dan LinkedIn Profile/Company).

Audit komprehensif ini dilakukan dengan memadukan **5 SKILL UTAMA COOCA** secara simultan guna memastikan kepatuhan terhadap batasan ketat masing-masing platform (terutama **panjang karakter caption** dan **kuota hashtag**), perlindungan keamanan multi-tenant & anti-fraud, adaptabilitas sadar-konteks pada **20 sektor industri UMKM di 6 klaster**, keselarasan antarmuka **Bento Apple HIG v2.0**, serta kesiapan dwibahasa (**i18n ID & EN**).

---

## 2. Metodologi & 5 Dimensi Audit

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                             5 DIMENSI AUDIT COOCA                                │
├──────────────────────────────────────────────────────────────────────────────────┤
│ 1. 🔄 System Workflow & End-to-End Tracing                                      │
│    (User → Blade/Alpine → Route → Controller → Validation → Service → API Queue) │
├──────────────────────────────────────────────────────────────────────────────────┤
│ 2. 🛡️ Security, Multi-Tenant Isolation & Anti-Fraud                             │
│    (Anti-SSRF, DOM XSS Sanitization, Rate Limiting, Maker-Checker, Anti-Phishing)│
├──────────────────────────────────────────────────────────────────────────────────┤
│ 3. 🏢 Multi-Industry Context & Dynamic Auto-Hiding (20 Sektor di 6 Klaster)      │
│    (Apotek/BPOM, Klinik/Rekam Medis, Bengkel/UU PDP Nopol, Salon, F&B, Mfgr)     │
├──────────────────────────────────────────────────────────────────────────────────┤
│ 4. 🎨 UI Panel Consistency & Information Architecture                            │
│    (3-Panel Alignment [Admin/Owner/Customer], Module Header & Tabs Navigation)   │
├──────────────────────────────────────────────────────────────────────────────────┤
│ 5. ⚡ COOCA Agent Directive (Bento HIG XXL, Real-Time Polling & Full i18n ID/EN) │
│    (Zero-Manual UI, Anti-Hyperbole, Zero Plaintext Secrets, Storage Auto-Pruning)│
└──────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Matriks Spesifikasi Batasan Karakter & Tagar Lintas Platform

Setiap platform media sosial memiliki aturan arsitektur yang sangat berbeda terkait muatan teks. Ketidaksesuaian parameter akan langsung menghasilkan respons HTTP 400 (*Bad Request / Graph API Exception*) dari server media sosial:

| Platform / Channel | Batas Karakter Resmi API | Rekomendasi COOCA (Conversion & UX) | Batas Tagar Resmi API | Batas Tagar Aturan Bisnis COOCA | Solusi Fleksibilitas Caption |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Meta Threads** | **500 Karakter** | 200–350 Karakter | 1 Topik Resmi API | **Maks. 5 Tagar Unik** | Jika caption utama $> 500$, beri peringatan ramah dan izinkan input caption terpisah khusus Threads ($\le 500$ chars). |
| **Instagram** | **2.200 Karakter** | 150–500 Karakter | Maks. 30 Tagar | **Maks. 5 Tagar Unik** | Dapat menggunakan caption utama atau custom caption feed/carousel/reels. |
| **TikTok** | **2.200 Karakter** | 100–300 Karakter | Dihitung dlm Karakter | **Maks. 5 Tagar Unik** | Dapat menggunakan caption utama atau custom caption video/photo. |
| **LinkedIn** | **3.000 Karakter** | 800–1.500 Karakter | Bebas (Ideal: 3–5) | **Maks. 5 Tagar Unik** | Dapat menggunakan caption utama atau custom caption artikel B2B. |
| **Facebook Page** | **63.206 Karakter** | < 5.000 Karakter | Bebas (Ideal: 2–3) | **Maks. 5 Tagar Unik** | Dapat menggunakan caption utama panjang (hingga 5.000+ chars). |

---

## 4. Tabel Rinci Temuan Audit 5 Dimensi

### Dimensi 1: 🔄 System Workflow & Aturan Medsos

| Kode | Temuan Masalah & Lokasi Berkas | Akar Masalah (Root Cause) | Dampak & Rekomendasi Solusi | Severity |
| :--- | :--- | :--- | :--- | :---: |
| **WF-01** | **Penanganan Batas 500 Karakter Threads:**<br>Lokasi: `resources/views/app/social_media/posts.blade.php`<br>Saat caption utama melebihi 500 karakter dan Threads dipilih, belum ada peringatan eksplisit dan panduan membuat caption terpisah. | Ketiadaan evaluator reaktif Threads pada input caption utama. | **Solusi:** Tampilkan banner peringatan cerdas Threads $> 500$ chars + tombol 1-klik `[ + Buat Caption Khusus Threads ]`. Jika caption terpisah diisi $\le 500$ chars, pos Threads dinyatakan valid dan pos saluran lain tetap dapat menikmati teks panjang. | `P1 - Tinggi` |
| **WF-02** | **Kustomisasi Caption per Saluran (*Channel Override*) Tanpa Validator Terisolasi:**<br>Lokasi: `posts.blade.php` (Line 785)<br>Textarea custom caption per channel belum memiliki live counter karakter (`X / Limit`) dan tagar (`Tagar: X / 5`). | Komponen override hanya menyediakan textarea polos tanpa binding penghitung lokal. | **Solusi:** Tambahkan live indicator karakter & tagar independen per kartu channel override serta tombol `[ Salin dari Caption Utama ]`. | `P2 - Sedang` |
| **WF-03** | **Validasi Backend Caption Efektif per Target:**<br>Lokasi: `SocialMediaWebController.php` (Line 444)<br>Pemeriksaan batas karakter belum mengecek `$effectiveCaption` per target platform sebelum dispatch ke antrean job. | Validasi saat ini hanya memeriksa string global `$validated['content']`. | **Solusi:** Loop setiap target account, tentukan `$effectiveCaption`, lalu validasi terhadap `CAPTION_LIMITS[$channel]`. | `P1 - Tinggi` |

---

### Dimensi 2: 🛡️ Security, Multi-Tenant Isolation & Anti-Fraud

| Kode | Temuan Masalah & Lokasi Berkas | Akar Masalah (Root Cause) | Dampak & Risiko Keamanan | Severity |
| :--- | :--- | :--- | :--- | :---: |
| **SEC-01** | **Residu Double Submit pada Form Multipart Besar (100MB):**<br>Lokasi: `posts.blade.php`<br>Tombol submit hanya memiliki `:disabled="isSubmitting"`. | Ketiadaan fullscreen upload progress overlay. | **Solusi:** Tambahkan indikator progress spinner dan kunci form saat upload berlangsung. | `P2 - Sedang` |
| **SEC-02** | **Isolasi Multi-Tenant Context::requireBusiness() Telah Teruji 100%:**<br>Lokasi: `SocialMediaWebController.php`, `SocialMediaService.php`<br>Semua operasi postingan, akun, komentar, dan analitik di-scope ketat ke `business_id` aktif. | Arsitektur controller telah mematuhi standar *Hard Guardrail Tenant Isolation*. | Tidak ditemukan kebocoran cross-tenant data. Status: **TERVERIFIKASI AMAN**. | `INFO - Aman` |
| **SEC-03** | **Anti-SSRF Protection & Anti-Phishing Bank Account Scanner Berjalan Baik:**<br>Lokasi: `SocialMediaWebController.php`<br>Memblokir protokol non-HTTPS, IP privat RFC 1918, metadata cloud `169.254.x.x`, serta menahan pos dengan rekening bank liar ke mode Maker-Checker. | Regex heuristik dan filter IP terintegrasi di storePost handler. | Melindungi kasir/staf dari skema fraud phishing dan melindungi server internal dari SSRF. | `INFO - Aman` |

---

### Dimensi 3: 🏢 Multi-Industry System Audit (20 Sektor di 6 Klaster)

| Sektor Industri / Klaster | Status Guardrail Saat Ini | Rekomendasi Penegakan & Banner Kontekstual | Do's & Don'ts |
| :--- | :---: | :--- | :--- |
| **Apotek & Toko Obat** (`retail_pharmacy`) | ✅ Aktif | Banner Peringatan BPOM & Larangan Iklan Obat Keras (Daftar G) / Antibiotik tanpa resep. | **Don't:** Menjanjikan klaim medis instan atau menjual obat resep di feed publik. |
| **Klinik & Layanan Medis** (`health_*`) | ⚠️ Perlu Ditambahkan | Wajib mengantongi persetujuan tertulis (*informed consent*) pasien sebelum menayangkan foto tindakan medis/bedah atau wajah pasien (UU PDP & Etika Medis). | **Don't:** Mempublikasikan riwayat rekam medis dan foto organ tanpa izin tertulis. |
| **Bengkel & Detailing** (`service_workshop`) | ✅ Aktif | Peringatan Sensor Plat Nomor Polisi (Nopol) & Wajah Pelanggan demi privasi UU PDP. | **Do:** Blur plat nomor kendaraan sebelum mengunggah materi foto servis. |
| **Salon & Barbershop** (`service_barbershop`) | ✅ Aktif | Pengingat izin pelanggan sebelum menayangkan foto before-after transformasi treatment. | **Do:** Minta persetujuan lisan/tertulis pelanggan sebelum difoto. |
| **Petshop & Klinik Hewan** (`service_petshop`) | ⚠️ Perlu Ditambahkan | Peringatan Larangan Jual-Beli Satwa Dilindungi & Obat Keras Hewan tanpa resep dokter hewan (UU Peternakan & Kesehatan Hewan). | **Don't:** Menjual hewan dilindungi atau antibiotik hewan secara bebas. |
| **Kuliner & F&B** (`fnb_*`) | ✅ Aktif | Banner Waktu Emas Posting (10:30–11:30 WIB jelang makan siang & 16:30–18:00 WIB jelang makan malam) untuk konversi order maksimal. | **Do:** Jadwalkan promo pada jam lapar audiens. |
| **Manufaktur, Konveksi & Kreatif** (`mfg_*`) | ✅ Aktif | Pengingat Hak Cipta Klien & Perjanjian Kerahasiaan (NDA) desain seragam/maklon. | **Don't:** Menjadikan portofolio pesanan yang terikat NDA sebagai materi promosi publik. |
| **Retail & Fashion** (`retail_*`) | ⚠️ Perlu Ditambahkan | Edukasi etika promosi: Hindari klaim diskon palsu (fake original price) sesuai UU Perlindungan Konsumen. | **Do:** Cantumkan harga asli dan harga promo yang transparan. |

---

### Dimensi 4: 🎨 UI Panel Consistency & Information Architecture

| Aspek UI / IA | Status Evaluasi | Rekomendasi Standarisasi |
| :--- | :---: | :--- |
| **Konsistensi 3 Panel** | ✅ Rapi | Konfigurasi platform tersentralisasi di Admin Hub (`/admin/settings?tab=social`), operasional toko di Owner Cockpit (`/social-media`), dan Storefront terpisah bersih. |
| **3-Baris Page Header & Title** | ✅ Sesuai HIG | Menggunakan komponen `<x-module-header module="communication" ...>` dengan judul lapang dan deskripsi fungsional. |
| **Top-Level Module Tabs** | ✅ Sesuai Standar | Menggunakan `<x-module-tabs module="communication" />` yang menghubungkan WhatsApp, Media Sosial, dan Landing Page secara konsisten. |
| **Sub-Tab Navigation Bar** | ✅ Rapi | Navigasi sub-tab (`Koneksi Akun`, `Posting Konten`, `Kalender`, `Kotak Masuk`, `Analitik`) terdistribusi konsisten. |
| **Bento Apple HIG v2.0 XXL** | ✅ Sesuai Mandat | Modal komposer pos menggunakan ukuran `max-w-5xl xl:max-w-6xl` dengan 2 kolom (form kontrol di kiri, live preview smartphone di kanan). |

---

### Dimensi 5: ⚡ COOCA Agent Directive (i18n & Real-Time)

| Aspek Direktif | Kondisi Saat Ini | Rekomendasi Remidiasi |
| :--- | :--- | :--- |
| **Multi-Bahasa (i18n & l10n)** | Ditemukan > 80 hardcoded string bahasa Indonesia di dalam 5 file view Blade. Kamus `lang/id/social_media.php` baru berisi 12 baris dasar. | Ekstraksi seluruh teks ke kamus modular `lang/id/social_media.php` dan `lang/en/social_media.php`, ganti pemanggilan Blade dengan `{{ __('social_media....') }}`. |
| **Real-Time Data (Anti-Reload)** | Kotak masuk komentar (`inbox.blade.php`) dan analitik (`insights.blade.php`) telah menggunakan AJAX tanpa `location.reload()`. Namun belum ada auto-polling latar belakang saat pengguna membuka inbox. | Tambahkan smart polling 30-45 detik pada `inbox.blade.php` dengan guard `!document.hidden`. |
| **Zero Plaintext Credentials** | Kredensial API di Admin Settings Hub telah menggunakan input tipe password dengan tombol toggle show/hide dan enkripsi database. | Mempertahankan perlindungan masking `••••••••` pada seluruh token. |
| **Storage & Pruning Lifecycle** | Berkas foto/video yang diunggah ke local server otomatis dihapus (`Storage::disk('public')->delete(...)`) setelah sukses dipublikasikan ke Meta/TikTok API. | Alur auto-pruning sudah optimal dan menghemat kuota hosting UMKM. |
