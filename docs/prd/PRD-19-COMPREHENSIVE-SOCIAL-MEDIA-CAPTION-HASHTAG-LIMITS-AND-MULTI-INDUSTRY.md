# PRD-19: Standardisasi Batasan Karakter Caption, Pemisahan Caption per Saluran (1-for-All vs Per-Channel), Validasi Tagar & Multi-Industri

> **ID Dokumen:** `PRD-19-COMPREHENSIVE-SOCIAL-MEDIA-CAPTION-HASHTAG-LIMITS-AND-MULTI-INDUSTRY`  
> **Status Dokumen:** APPROVED FOR EXECUTION  
> **Domain Terkait:** `app/Domain/SocialMedia/`, `resources/views/app/social_media/`, `lang/id/`, `lang/en/`  
> **Target Pengguna:** Pemilik Toko UMKM, Staf Pemasaran Medsos, Admin Toko, Super Admin Cooca  
> **Standar Rujukan:** `docs/agent.md`, `docs/SYSTEM_GUIDE.md`, Bento Apple HIG v2.0, Meta Graph API v26.0, TikTok for Business API, LinkedIn Marketing API

---

## 1. Latar Belakang & Pernyataan Masalah (Problem Statement)

Modul **Media Sosial Omnichannel COOCA (`resources/views/app/social_media`)** memfasilitasi publikasi konten serentak ke berbagai platform media sosial dari satu antarmuka komposer terpadu (*Unified Composer*).

Platform-platform media sosial memiliki **standar batas karakter resmi API**:
- **Meta Threads**: **Maksimal 500 karakter**
- **Instagram**: **Maksimal 2.200 karakter**
- **TikTok**: **Maksimal 2.200 karakter**
- **LinkedIn**: **Maksimal 3.000 karakter**
- **Facebook Page**: **Maksimal 63.206 karakter** (Rekomendasi COOCA: < 5.000 karakter)

### Kebutuhan & Perilaku Pengguna:
1. **Pola Input Fleksibel**:
   - **Mode A (Mapping 1 Caption untuk Semua Saluran)**: Pengguna cukup menulis 1 caption utama yang otomatis disebarkan ke semua saluran terpilih.
   - **Mode B (Input Caption Terpisah per Saluran)**: Pengguna dapat menulis teks kustom untuk masing-masing saluran (misal: caption pendek & punchy untuk Threads, caption deskriptif untuk Instagram, artikel profesional untuk LinkedIn).
2. **Penanganan Batas 500 Karakter Threads**:
   - Jika caption utama $> 500$ karakter dan Threads dipilih:
     - Sistem memberikan peringatan khusus bahwa Threads memiliki batas maksimal 500 karakter.
     - Sistem memberikan tombol cepat 1-klik untuk **"Buat Caption Terpisah untuk Threads"** (atau menyalin dan memotong caption utama).
     - Jika pengguna telah mengisi caption terpisah untuk Threads yang $\le 500$ karakter, postingan Threads dinyatakan **VALID & LOLOS**, sementara saluran lain (FB, IG, LinkedIn) tetap menggunakan caption utama yang lebih panjang.
3. **Validasi Tagar COOCA (Maks. 5 Tagar Unik)**:
   - Ditegakkan pada caption utama maupun pada setiap caption terpisah per saluran dengan normalisasi *case-insensitive*.
4. **Sadar Konteks Industri (20 Sektor di 6 Klaster)**:
   - Penegakan panduan etika & regulasi (Apotek: BPOM, Klinik: Informed Consent & UU PDP, Bengkel: Sensor Nopol, Petshop: Satwa Dilindungi, F&B: Golden Hours, Manufaktur: Hak Cipta & NDA).
5. **Multi-Bahasa (i18n)**:
   - Seluruh teks antarmuka Blade bermigrasi penuh ke kamus `lang/id/` dan `lang/en/`.

---

## 2. Sasaran Produk & Metrik Keberhasilan (OKRs)

| Sasaran Produk | Metrik Keberhasilan | Target |
| :--- | :--- | :---: |
| **Pencegahan Error API Threads & Lintas Platform** | Peringatan cerdas saat caption Threads > 500 chars dengan opsi caption terpisah instan | 0 Error API Caption |
| **Fleksibilitas Input Caption** | Dukungan penuh 1-caption mapping maupun caption terpisah per-channel | 100% Fleksibel |
| **Kepatuhan Tagar** | Penegakan aturan maksimal 5 tagar unik di frontend & backend | 100% Terpatuhi |
| **Sadar Konteks Industri** | Penegakan otomatis banner edukasi hukum/etika untuk 20 sektor industri | Aktif 20 Sektor |
| **Kepatuhan Dwibahasa** | Migrasi 100% teks hardcoded ke kamus terjemahan `lang/id/` & `lang/en/` | 100% Terlokalisasi |
| **Kesiapan Pengujian** | Test suite otomatis media sosial lulus 100% | 0 Failure, 0 Error |

---

## 3. Spesifikasi Fungsional (Functional Requirements)

### 3.1 Peringatan Cerdas Batas Karakter Threads & Platform Terpilih
- Jika Threads dipilih dan caption utama $> 500$ karakter:
  - Muncul banner peringatan:  
    *"Caption utama Anda berisi :length karakter. Khusus Threads, panjang maksimal adalah 500 karakter. Silakan buat caption terpisah khusus Threads di bawah atau perpendek caption utama."*
  - Tombol aksi: `[ + Buat Caption Khusus Threads ]` yang otomatis membuka tab/accordion kustomisasi dan memfokuskan kursor ke textarea Threads.
  - Jika textarea khusus Threads telah diisi dengan teks $\le 500$ karakter, peringatan berubah menjadi badge sukses hijau:  
    *“Caption terpisah untuk Threads aktif (:length / 500 karakter).”*

### 3.2 Mode 1-Caption untuk Semua vs Caption Terpisah per Saluran
- Di dalam form komposer, pengguna memiliki kontrol penuh:
  - **Caption Utama**: Digunakan sebagai default untuk seluruh saluran yang dipilih.
  - **Kustomisasi per Saluran (Tab/Accordion)**:
    - Menampilkan kartu untuk setiap akun/saluran terpilih.
    - Setiap kartu memiliki:
      - Header: Ikon platform, nama akun, dan batas karakter resmi (`Maks. 500 Karakter` untuk Threads, `Maks. 2.200` untuk Instagram/TikTok, `Maks. 3.000` untuk LinkedIn, `Maks. 5.000` untuk Facebook).
      - Textarea input independen.
      - Action: `[ Salin dari Caption Utama ]` untuk mempercepat penulisan.
      - Live counter karakter: `X / <Limit_Platform>`.
      - Live counter tagar: `Tagar: X / 5`.

### 3.3 Backend Request Validation (`SocialMediaWebController` & `SocialMediaContentValidator`)
- Saat request `POST /social-media/posts` diproses:
  1. Validasi tagar 5 COOCA pada caption utama `$request->input('content')`.
  2. Untuk setiap target account yang dipilih:
     - Tentukan caption efektif: `$effectiveCaption = $customCaptions[$accountId] ?? $mainCaption;`
     - Validasi panjang `$effectiveCaption` terhadap `CAPTION_LIMITS[$channel]`.
     - Jika `$channel === 'threads'` dan `$effectiveCaption > 500`:
       Tolak dengan error: *"Saluran Threads membatasi caption maksimal 500 karakter (saat ini: :count karakter). Silakan gunakan input caption terpisah khusus Threads."*
     - Validasi tagar 5 COOCA pada `$effectiveCaption`.

### 3.4 Penegakan Guardrail 20 Sektor Industri (6 Klaster)
- Berdasarkan `$business->template_code`, `$business->industry_category`, dan `$business->industry`:
  - **Apotek**: Regulasi BPOM & Larangan promosi obat keras / antibiotik tanpa resep.
  - **Klinik**: Wajib *informed consent* tertulis pasien sebelum unggah foto tindakan medis/wajah (UU PDP).
  - **Bengkel**: Wajib sensor/blur plat nomor polisi (nopol) & area servis.
  - **Salon**: Wajib izin pelanggan untuk foto before-after potret treatment.
  - **Petshop**: Larangan jual-beli satwa dilindungi dan obat keras hewan.
  - **Kuliner (F&B)**: Rekomendasi Waktu Emas Posting (10:30–11:30 & 16:30–18:00 WIB).
  - **Manufaktur & Konveksi**: Peringatan Hak Cipta Klien & NDA Desain Maklon.
  - **Retail & Fashion**: Transparansi diskon asli tanpa *fake strike price*.

### 3.5 Ekstraksi Kamus Dwibahasa (i18n & l10n ID/EN)
- Seluruh teks statis pada 5 file view Blade (`index`, `posts`, `calendar`, `inbox`, `insights`) diekstraksi ke `lang/id/social_media.php` dan `lang/en/social_media.php`.

### 3.6 Real-Time Smart Background Polling pada Inbox
- `inbox.blade.php` menjalankan background polling setiap 30 detik saat tab browser aktif (`!document.hidden`).

---

## 4. Kriteria Penerimaan (Acceptance Criteria)

1. **AC-01 (1-Caption Mapping)**: Jika caption utama $\le 500$ karakter, 1 caption berhasil dipublikasikan ke semua saluran terpilih (Threads, FB, IG, TikTok, LinkedIn) tanpa perlu mengisi caption terpisah.
2. **AC-02 (Threads > 500 Chars with Separate Caption)**: Jika caption utama 1.000 karakter dan Threads dipilih:
   - Sebelum caption terpisah diisi: Muncul peringatan khusus Threads > 500 chars.
   - Setelah caption terpisah Threads diisi 350 karakter: Postingan lolos validasi, Threads menerima 350 karakter, Facebook menerima 1.000 karakter.
3. **AC-03 (Hashtag Cap)**: Maksimal 5 tagar unik pada caption utama maupun caption terpisah per saluran.
4. **AC-04 (Industry Guardrails)**: Sektor Apotek, Klinik, Bengkel, Salon, Petshop, F&B, Manufaktur menampilkan banner edukasi hukum/etika yang sesuai.
5. **AC-05 (Multi-Language)**: Beralih bahasa ke English (`/locale/en`) mengubah 100% teks ke Bahasa Inggris.
6. **AC-06 (Automated Testing)**: Seluruh test suite di `tests/Feature/SocialMedia/*` lulus 100% (0 error).
