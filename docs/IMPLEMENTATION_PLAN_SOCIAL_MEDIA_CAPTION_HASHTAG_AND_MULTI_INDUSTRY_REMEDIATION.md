# RENCANA IMPLEMENTASI TEKNIS (IMPLEMENTATION PLAN)
## Remidiasi Modul Media Sosial Omnichannel: Dynamic Caption Limits, Caption Terpisah per Saluran, Tagar 5 COOCA, Guardrail 20 Sektor Industri & Multi-Bahasa

> **ID Dokumen:** `IMPLEMENTATION-PLAN-SOCMED-CAPTION-HASHTAG-I18N`  
> **Status:** READY FOR EXECUTION  
> **Rujukan PRD:** `docs/prd/PRD-19-COMPREHENSIVE-SOCIAL-MEDIA-CAPTION-HASHTAG-LIMITS-AND-MULTI-INDUSTRY.md`  
> **Rujukan Audit:** `docs/AUDIT_KOMPREHENSIF_MEDIA_SOSIAL_CAPTION_HASHTAG_DAN_MULTI_INDUSTRI.md`  
> **Standar Kualitas:** Bento Apple HIG v2.0, Zero Plaintext Secrets, 100% Automated Tests Pass.

---

## 1. Daftar Berkas Terdampak (Affected Files)

| No | Path Berkas | Tipe Perubahan | Deskripsi Perubahan |
| :---: | :--- | :---: | :--- |
| **1** | `lang/id/social_media.php` | Update / Expand | Kamus bahasa Indonesia lengkap (80+ keys): cockpit, composer, 1-caption vs separate captions, Threads 500 chars warning, guardrails 20 sektor, filter status, notifikasi. |
| **2** | `lang/en/social_media.php` | Update / Expand | Kamus bahasa Inggris lengkap (80+ keys) untuk modul medsos secara simetris. |
| **3** | `resources/views/app/social_media/posts.blade.php` | Refactor / Feature | Peringatan khusus Threads 500 chars, tombol 1-klik buat caption terpisah Threads, counter karakter & tagar independen per saluran, ekspansi guardrail 20 sektor industri, dan lokalisasi penuh. |
| **4** | `resources/views/app/social_media/index.blade.php` | Refactor / i18n | Lokalisasi seluruh teks ke helper i18n dwibahasa, perapihan bento card onboarding. |
| **5** | `resources/views/app/social_media/calendar.blade.php` | Refactor / i18n | Lokalisasi kalender dan label status ke helper i18n. |
| **6** | `resources/views/app/social_media/inbox.blade.php` | Refactor / Realtime | Lokalisasi i18n dan penambahan smart background auto-polling 30 detik. |
| **7** | `resources/views/app/social_media/insights.blade.php` | Refactor / i18n | Lokalisasi tabel metrik analitik ke helper i18n. |
| **8** | `app/Http/Controllers/Web/SocialMedia/SocialMediaWebController.php` | Validation & Logic | Validasi channel-specific caption limit (Threads 500, IG 2200, TikTok 2200, LinkedIn 3000, FB 5000) dan fallback caption terpisah. |
| **9** | `app/Domain/SocialMedia/Validation/SocialMediaContentValidator.php` | Validation Logic | Pemastian validasi caption efektif per-saluran dan tagar 5 COOCA. |
| **10** | `tests/Feature/SocialMedia/SocialMediaContentValidatorTest.php` | Test Expansion | Penambahan test case untuk validasi per-channel caption dan Threads 500 chars limit. |
| **11** | `docs/AiWorkHistory.md` & `docs/system/modules/social-media.md` | Documentation | Pencatatan riwayat pekerjaan AI dan pembaruan arsitektur sistem. |

---

## 2. Rincian Roadmap Implementasi 5 Tahap (5-Phase Execution Plan)

### FASE 1: Kamus Multi-Bahasa Terpadu (i18n & l10n ID & EN)
- **Target File:** `lang/id/social_media.php` dan `lang/en/social_media.php`.
- **Tindakan Teknis:**
  1. Menyusun kamus lengkap dwibahasa untuk semua teks, peringatan Threads, opsi 1-caption vs separate caption, dan guardrail sektor bisnis.

### FASE 2: Backend Validation & Controller Hardening
- **Target File:** `SocialMediaWebController.php` & `SocialMediaContentValidator.php`.
- **Tindakan Teknis:**
  1. Di `SocialMediaWebController::storePost()`:
     - Iterasi setiap target account terpilih.
     - Ambil caption efektif: `$effCaption = !empty($validated['custom_captions'][$acc->id]) ? $validated['custom_captions'][$acc->id] : $validated['content'];`
     - Validasi panjang `$effCaption` terhadap batasan channel (`threads`: 500, `instagram`: 2200, `tiktok`: 2200, `linkedin`: 3000, `facebook`: 50000).
     - Jika `$acc->platform === 'threads'` dan `mb_strlen($effCaption) > 500`: Return redirect back dengan pesan error informatif agar pengguna menggunakan caption terpisah untuk Threads.
     - Validasi tagar 5 COOCA pada `$effCaption`.

### FASE 3: UI Komposer Bento Apple HIG (Posts View)
- **Target File:** `resources/views/app/social_media/posts.blade.php`.
- **Tindakan Teknis:**
  1. Menambahkan state Alpine.js:
     - `customCaptions: { ... }`
     - Deteksi `hasThreadsSelected`, `isThreadsOverLimit`, `threadsSeparateCaptionActive`.
  2. Menampilkan Banner Peringatan Cerdas Threads jika caption utama $> 500$ karakter dan Threads dipilih tanpa caption terpisah yang valid.
  3. Menyediakan tombol `[ + Buat Caption Khusus Threads ]` yang membuka accordion kustomisasi dan memfokuskan kursor.
  4. Menyediakan tombol `[ Salin dari Caption Utama ]` di setiap kartu kustomisasi saluran.
  5. Menambahkan live counter karakter (`X / Limit`) dan tagar (`Tagar: X / 5`) pada setiap custom caption saluran.
  6. Ekspansi Guardrail 20 Sektor Industri (Klinik, Petshop, Retail, Apotek, Bengkel, Salon, F&B, Manufaktur).
  7. Migrasi seluruh teks ke `{{ __('social_media....') }}`.

### FASE 4: Smart Auto-Polling & Lokalisasi 4 View Blade Lainnya
- **Target Files:** `index.blade.php`, `calendar.blade.php`, `inbox.blade.php`, `insights.blade.php`.
- **Tindakan Teknis:**
  1. Pada `inbox.blade.php`: Tambahkan auto-polling 30 detik saat tab aktif.
  2. Ganti seluruh teks statis bahasa Indonesia dengan helper `{{ __('social_media....') }}`.

### FASE 5: Automated Testing Suite & Dokumentasi Sistem
- **Target Files:** `tests/Feature/SocialMedia/*`, `docs/AiWorkHistory.md`, `docs/system/modules/social-media.md`.
- **Tindakan Teknis:**
  1. Jalankan `php artisan test --filter=SocialMedia`.
  2. Pastikan 100% tes lulus (0 error, 0 failure).
  3. Catat entri lengkap di `docs/AiWorkHistory.md` dan perbarui dokumentasi sistem di `docs/system/modules/social-media.md`.
