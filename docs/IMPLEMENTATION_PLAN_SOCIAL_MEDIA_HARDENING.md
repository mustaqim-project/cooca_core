# Rencana Aksi & Implementasi: Hardening Keamanan Cyber, Proteksi Fraud Internal & Standar Sadar Konteks 20 Industri pada Modul Media Sosial Omnichannel

> **Referensi Dokumen:** [`docs/prd/PRD-12-SOCIAL-MEDIA-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-12-SOCIAL-MEDIA-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md)  
> **ID Dokumen:** `PLAN-12-SOCIAL-MEDIA-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY`  
> **Penanggung Jawab:** Security Auditor, Principal Laravel Architect, UI/UX Engineer, QA Engineer  
> **Status:** FASE 1 & FASE 2 COMPLETED & VERIFIED (Lolos 100% Test Suite) | FASE 3–5 PENDING USER DIRECTIVE

---

## 1. Ringkasan Eksekutif & Sasaran Teknis

Rencana implementasi bertahap ini dirancang untuk mengeksekusi perbaikan menyeluruh terhadap modul **Media Sosial Omnichannel (`resources/views/app/social_media`)** dan komponen backend terkait (`SocialMediaWebController`, `SocialMediaService`, `SocialMediaContentValidator`, `PublishSocialMediaTargetJob`, `routes/owner.php`).

Perbaikan ini menyelesaikan 7 masalah inti hasil audit sistem:
1. **Pemisahan Izin RBAC & Eliminasi Permission Coupling:** Menghapus ketergantungan keliru pada middleware `whatsapp.view` dan mengukuhkan izin resmi `social_media.view` dan `social_media.manage`.
2. **Mitigasi Serangan Server-Side Request Forgery (SSRF) pada URL Media:** Memvalidasi `media_url` agar wajib `https://` dan menolak resolusi IP lokal (`localhost`, `127.0.0.1`), LAN privat (RFC 1918), dan link-local cloud metadata (`169.254.169.254`).
3. **Pemberantasan Celah DOM XSS & Penyehatan Kotak Masuk:** Mengganti penyisipan raw string dengan helper Blade `@js()` dan menghapus manipulasi `innerHTML` berbahaya pada `inbox.blade.php`.
4. **Pencegahan Penyalahgunaan API Rate Limit:** Menerapkan middleware throttling pada endpoint sinkronisasi metrik live (`throttle:10,1`) dan balas komentar (`throttle:15,1`).
5. **Proteksi Fraud Internal & Sabotase Akun (Maker-Checker):** Memberlakukan kendali ganda untuk staf kasir/staf non-owner (`pending_review` ➔ approval Owner), pendeteksi nomor rekening liar/phishing di caption, dan pencatatan jejak audit forensik `user_id`.
6. **Penyempurnaan Desain Bento Apple HIG v2.0:** Mengeliminasi native `alert()` dan `confirm()` ke `AppAlert`, memperluas modal sheet ke ukuran XXL (`max-w-5xl`/`xl:max-w-6xl`), dan menambahkan inspeksi aspek rasio video (16:9 vs 9:16).
7. **Penegakan Pedoman 20 Sektor Industri:** Peringatan jam istirahat (*Quiet Hours* 22:00–06:00 WIB), peringatan kepatuhan regulasi farmasi BPOM & Meta Health Policy untuk Apotek, serta panduan sensor plat nomor kendaraan bengkel dan privasi salon.

---

## 2. Peta Fase Implementasi (5 Tahapan Terstruktur)

```text
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Hardening Keamanan Cyber, Anti-SSRF, DOM XSS & Rate Limiting   │
│         (Prioritas P1 - Kritis)                                        │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Pemisahan Hak Akses RBAC & Proteksi Fraud Maker-Checker Konten │
│         (Prioritas P1 - Kritis)                                        │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Penyempurnaan Desain Bento Apple HIG v2.0 & Modal Sheet XXL    │
│         (Prioritas P2 - Menengah)                                      │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Penegakan Panduan & Guardrail Sadar Konteks 20 Sektor Industri │
│         (Prioritas P2 - Menengah)                                      │
├────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Pengujian Otomatis Lolos 100%, Verifikasi Regresi & Dokumentasi│
│         (Prioritas P1 - Kritis)                                        │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Teknis Eksekusi Per Fase

---

### FASE 1: Hardening Keamanan Cyber, Anti-SSRF, DOM XSS & Rate Limiting (P1)

**Tujuan:** Menutup celah keamanan web aplikasi (OWASP Top 10) pada pengunggahan URL media eksternal, interaksi Javascript di kotak masuk, dan konsumsi API pihak ketiga.

#### Langkah 1.1: Validasi Anti-SSRF pada Media URL Komposer
* **Berkas:** [`app/Http/Controllers/Web/SocialMedia/SocialMediaWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/SocialMedia/SocialMediaWebController.php)
* **Tindakan:**
  - Tambahkan validasi DNS dan IP filter pada method `storePost()` saat parameter `media_url` diisi:
    ```php
    if (!empty($validated['media_url'])) {
        $url = trim($validated['media_url']);
        $parsed = parse_url($url);
        if (($parsed['scheme'] ?? '') !== 'https') {
            return redirect()->back()->withInput()->with('error', 'URL media wajib menggunakan protokol aman https://');
        }
        $host = $parsed['host'] ?? '';
        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return redirect()->back()->withInput()->with('error', 'URL media tidak valid atau mengarah ke alamat jaringan lokal/privat.');
        }
    }
    ```

#### Langkah 1.2: Sanitasi DOM XSS & Keamanan Skrip Kotak Masuk
* **Berkas:** [`resources/views/app/social_media/inbox.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/inbox.blade.php)
* **Tindakan:**
  - Ganti pemanggilan `@click="prepareReply(...)"` yang menggunakan raw `addslashes()` dengan data attributes atau `@js()`:
    ```html
    <button type="button"
        @click="prepareReply(@js($c->id), @js($c->sender_name ?: 'Pengguna ' . ucfirst($c->platform)), @js($c->message), @js($c->platform))"
        class="h-8 px-3.5 rounded-[9px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all inline-flex items-center gap-1.5 shadow-sm">
        <i data-lucide="corner-up-left" class="w-3.5 h-3.5"></i>
        <span>Balas</span>
    </button>
    ```
  - Hilangkan manipulasi `badge.innerHTML` tidak aman pada baris 299, gunakan penataan class list dan textContent terisolasi.

#### Langkah 1.3: Penambahan Rate Limiting pada Endpoint Sensitif
* **Berkas:** [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php)
* **Tindakan:**
  - Tambahkan middleware `throttle` pada endpoint reply komentar dan sinkronisasi wawasan metrik:
    ```php
    Route::post('/comments/{comment}/reply', [SocialMediaWebController::class, 'replyComment'])
        ->middleware('throttle:15,1')
        ->name('comments.reply');
        
    Route::post('/insights/{post}/sync', [SocialMediaWebController::class, 'syncInsights'])
        ->middleware('throttle:10,1')
        ->name('insights.sync');
    ```

---

### FASE 2: Pemisahan Hak Akses RBAC & Proteksi Fraud Maker-Checker (P1)

**Tujuan:** Mengisolasi permission modul media sosial serta mencegah sabotase akun resmi toko oleh staf non-owner.

#### Langkah 2.1: Pemisahan Permission di Route & RBAC Seeder
* **Berkas:** [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php) & [`database/seeders/RbacSeeder.php`](file:///c:/laragon/www/cooca_core/database/seeders/RbacSeeder.php)
* **Tindakan:**
  - Ubah prefix route group di `routes/owner.php`:
    ```php
    Route::prefix('social-media')->name('social-media.')->middleware('require.permission:social_media.view')->group(function (): void {
    ```
  - Berikan middleware `require.permission:social_media.manage` pada aksi publikasi, hapus akun, dan balas komentar.
  - Pastikan `RbacSeeder` dan fallback provider memberikan permission ini secara otomatis kepada peran `owner` dan `store_manager`.

#### Langkah 2.2: Migrasi Skema Basis Data & Model Eloquent
* **Berkas:** `database/migrations/2026_09_28_100000_add_audit_and_approval_to_social_media_posts.php` & [`app/Models/SocialMediaPost.php`](file:///c:/laragon/www/cooca_core/app/Models/SocialMediaPost.php)
* **Tindakan:**
  - Tambahkan kolom: `user_id`, `approval_status` (`approved`, `pending_review`, `rejected`), `reviewed_by`, `reviewed_at`, `rejection_reason`, `risk_flags`.
  - Pada `SocialMediaPost.php`, tambahkan relasi `author()` dan `reviewer()` ke model `User`.

#### Langkah 2.3: Logika Maker-Checker & Caption Phishing Heuristic Scanner
* **Berkas:** [`app/Http/Controllers/Web/SocialMedia/SocialMediaWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/SocialMedia/SocialMediaWebController.php)
* **Tindakan:**
  - Pada `storePost()`:
    ```php
    $isOwnerOrManager = Context::isOwner() || $request->user()->hasRole('store_manager');
    $approvalStatus = $isOwnerOrManager ? 'approved' : 'pending_review';
    
    // Heuristic Scan: Rekening Bank Liar
    $detectedBankAccounts = $this->scanBankAccountsInCaption($validated['content']);
    $registeredAccounts = \App\Models\FinanceAccount::where('business_id', $business->id)->pluck('account_number')->toArray();
    $unauthorizedAccounts = array_diff($detectedBankAccounts, $registeredAccounts);
    
    if (!empty($unauthorizedAccounts) && !$isOwnerOrManager) {
        $approvalStatus = 'pending_review';
        $riskFlags[] = 'unregistered_bank_account_detected';
    }
    ```
  - Tambahkan endpoint `POST /social-media/posts/{post}/approve` dan `POST /social-media/posts/{post}/reject` khusus Owner/Manager.

---

---

### FASE 3: Penyempurnaan Desain Bento Apple HIG v2.0 & Modal Sheet XXL (P2) - [SELESAI / COMPLETED]

**Tujuan:** Meningkatkan estetika visual antarmuka agar mematuhi standar Bento Apple HIG, menghindari dialog peramban usang, dan menyajikan modal berukuran nyaman.

#### Langkah 3.1: Penggantian Dialog Native Browser ke `AppAlert` - [SELESAI]
* **Berkas:** `resources/views/app/social_media/index.blade.php`, `inbox.blade.php`, `insights.blade.php`, `posts.blade.php`
* **Hasil:**
  - Seluruh dialog `alert()` dan `confirm()` native peramban telah dieleminasi 100%.
  - Pesan sukses dan gagal digantikan oleh `AppAlert.success()` dan `AppAlert.error()`.
  - Konfirmasi putus akun dan persetujuan maker-checker (`approve`/`reject`) diamankan via `AppAlert.confirm()` & `AppAlert.confirmSubmit()`.

#### Langkah 3.2: Perluasan Modal Sheet ke Standar Bento XXL - [SELESAI]
* **Berkas:** [`resources/views/app/social_media/posts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/posts.blade.php) & [`resources/views/app/social_media/inbox.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/inbox.blade.php)
* **Hasil:**
  - Kontainer modal komposer pos diperluas dari `max-w-3xl` menjadi `max-w-5xl xl:max-w-6xl` dengan struktur Bento 2 kolom (kiri: form saluran, media, caption & jadwal; kanan: guardrail & live smartphone preview).
  - Modal reply di `inbox.blade.php` diperluas dari `max-w-lg` ke `max-w-2xl` dengan kartu pratinjau komentar Apple HIG (avatar gradient, platform pill, quote styling).

#### Langkah 3.3: Deteksi Aspek Rasio Video Cerdas di Frontend - [SELESAI]
* **Berkas:** [`resources/views/app/social_media/posts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/posts.blade.php)
* **Hasil:**
  - Inspeksi HTML5 `<video>` saat pengunggahan berkas: membaca `videoWidth`, `videoHeight`, dan menghitung `videoRatio` serta boolean `isLandscapeVideo`.
  - Banner peringatan dinamis muncul jika video berorientasi Landscape dipilih untuk format Reels atau TikTok, serta chip hijau konfirmasi saat rasio vertikal optimal 9:16 terdeteksi.

---

### FASE 4: Penegakan Panduan & Guardrail Sadar Konteks 20 Sektor Industri (P2) - [SELESAI / COMPLETED]

**Tujuan:** Menjaga kepatuhan hukum dan citra merek toko UMKM pada 20 sektor bisnis.

#### Langkah 4.1: Banner Edukasi & Guardrail Sektor Kontekstual di Komposer - [SELESAI]
* **Berkas:** [`resources/views/app/social_media/posts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/posts.blade.php)
* **Hasil:**
  - Kartu edukasi Bento adaptif berdasarkan `$business->template_code`, `$business->industry_category`, dan `$business->industry`:
    - **Apotek & Toko Obat:** Peringatan merah kepatuhan BPOM & Meta Policy melarang promosi obat keras / Daftar G tanpa resep dokter.
    - **Bengkel & Auto Detailing:** Peringatan kepatuhan UU PDP menyamarkan / mem-blur plat nomor polisi kendaraan pelanggan.
    - **Salon, Barbershop & Kosmetik:** Peringatan izin foto dan persetujuan pelanggan untuk dokumentasi before-after.
    - **F&B, Resto & Cafe:** Panduan jam emas publikasi konten kuliner (10:30 & 16:30 WIB) untuk konversi order maksimal.
    - **Garment, Percetakan & Agency:** Peringatan hak cipta logo pesanan klien dan persetujuan non-disclosure (NDA).
    - **General / Sektor Lain:** Peringatan anti-fraud etika bisnis & larangan rekening pribadi di caption.

#### Langkah 4.2: Peringatan Jam Senyap (Quiet Hours Warning) - [SELESAI]
* **Berkas:** [`resources/views/app/social_media/posts.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/social_media/posts.blade.php)
* **Hasil:**
  - Getter reaktif Alpine.js `isQuietHours` mendeteksi penayangan pada rentang 22:00 s/d 06:00 WIB (baik instan 'Semua Sekarang', jadwal serentak, maupun per saluran).
  - Menampilkan banner soft orange: *"Peringatan Jam Senyap (22:00 - 06:00 WIB)"* menyarankan penjadwalan pada jam aktif audiens (08:00 - 21:00 WIB).

---

### FASE 5: Pengujian Otomatis Lolos 100%, Verifikasi & Dokumentasi (P1) - [SELESAI / COMPLETED]

**Tujuan:** Membuktikan bahwa seluruh perubahan bebas bug, lulus seluruh test cases, dan terdokumentasi rapi.

#### Langkah 5.1: Penambahan Test Case Fitur Baru - [SELESAI]
* **Berkas:** [`tests/Feature/SocialMedia/SocialMediaHardeningAndSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/SocialMedia/SocialMediaHardeningAndSecurityTest.php)
* **Cakupan Uji & Hasil (17 Test Methods, 77 Assertions):**
  1. **Anti-SSRF:**
     - Penolakan skema tidak aman `http://` (`test_anti_ssrf_rejects_insecure_http_url`).
     - Penolakan loopback & localhost `127.0.0.1` (`test_anti_ssrf_rejects_loopback_and_local_ip`).
     - Penolakan link-local cloud metadata `169.254.169.254` (`test_anti_ssrf_rejects_cloud_metadata_service`).
     - Penolakan seluruh blok privat RFC 1918: `10.0.0.1`, `172.16.0.10`, `192.168.1.1` (`test_anti_ssrf_rejects_rfc1918_private_ip_ranges`).
  2. **DOM XSS Sanitization:**
     - Menguji eliminasi `innerHTML` dan validasi `@js` escaping pada blade view (`test_inbox_blade_uses_safe_js_escaping_and_no_inner_html_injection`).
  3. **Rate Limiting Middleware:**
     - Memverifikasi pemasangan `throttle:15,1` pada reply komentar dan `throttle:10,1` pada sync insights (`test_rate_limiting_middleware_configured_on_routes`).
  4. **RBAC & Entitlement:**
     - Memblokir akses staf tanpa izin `social_media.view` ke cockpit media sosial (`test_user_without_social_media_view_permission_is_blocked`).
     - Memblokir staf kasir/gudang tanpa `social_media.manage` menyetujui atau menolak postingan (`test_staff_without_manage_permission_cannot_approve_or_reject_posts`).
  5. **Maker-Checker & Audit Trail:**
     - Postingan oleh pemilik toko (owner) langsung berstatus `approved` dengan audit trail (`reviewed_by`, `reviewed_at`) (`test_post_created_by_owner_is_approved_with_audit_trail`).
     - Postingan staf toko otomatis masuk antrean `pending_review` (`test_post_created_by_staff_triggers_maker_checker_pending_review`).
     - Owner dapat menyetujui dan mendispatch publikasi pos `pending_review` (`test_owner_can_approve_pending_post_and_dispatch`).
     - Owner dapat menolak postingan dengan alasan terdokumentasi (`test_owner_can_reject_pending_post_with_reason`).
  6. **Anti-Fraud Rekening Rogue:**
     - Caption yang memuat rekening bank di luar profil toko ditandai `unregistered_bank_account_detected` dan ditahan (`test_post_with_unregistered_bank_account_is_flagged_with_risk`).
     - Caption yang memuat rekening resmi merchant terdaftar lolos tanpa flag risiko fraud (`test_post_with_registered_business_bank_account_is_not_flagged_with_risk`).
  7. **Isolasi Multi-Tenant (Anti-IDOR / Anti-BOLA):**
     - Tenant B yang mencoba menyetujui atau menolak postingan Tenant A menerima respon HTTP 404 dan status postingan Tenant A tetap aman (`test_multi_tenant_isolation_prevents_tenant_b_from_approving_or_rejecting_tenant_a_post`).
  8. **Integritas Tampilan Antarmuka (Blade & Apple HIG Bento):**
     - Komposer merender kontainer Bento XXL (`max-w-5xl xl:max-w-6xl`), inspektor video (`videoRatio`, `isLandscapeVideo`), peringatan jam senyap (`isQuietHours`), dan konfirmasi `AppAlert.confirmSubmit` (`test_posts_index_view_renders_bento_xxl_guardrails_and_app_alert`).
     - Tampilan Inbox & Insights terbebas 100% dari dialog native `alert(` dan `confirm(` serta menggunakan notifikasi `AppAlert` (`test_inbox_and_insights_views_render_without_native_dialogs`).

#### Langkah 5.2: Eksekusi Test Suite & Hardening Final - [SELESAI]
* **Linting Sintaks:**
  - `php -l` dijalankan pada seluruh berkas controller, model, blade views, dan test cases: **100% Bebas Syntax Error**.
* **Eksekusi Test Suite Lengkap:**
  ```bash
  vendor/bin/phpunit tests/Feature/SocialMedia
  ```
  **Hasil:**
  - **Tests:** 69 total tests
  - **Assertions:** 399 assertions
  - **Failures:** 0
  - **Errors:** 0
  - **Status:** **100% PASSED (Green)**
* **Audit Kebersihan Kode Debug:**
  - Pemeriksaan `dd()`, `dump()`, dan `console.log()`: **Bersih 100%** (tidak ada artefak debugging di berkas produksi).

---

### RINGKASAN STATUS KESELURUHAN IMPLEMENTASI

| Fase | Nama Fase | Status | Keterangan |
|---|---|---|---|
| **Fase 1** | Remediasi Keamanan Siber & Proteksi Anti-Fraud | **SELESAI** | Anti-SSRF, DOM XSS Sanitization, Rate Limiting, Heuristic Anti-Fraud Rekening |
| **Fase 2** | Maker-Checker Approval & Audit Trail Multi-Tenant | **SELESAI** | Migrasi DB schema, flow approval, RBAC authorization, anti-IDOR isolation |
| **Fase 3** | UI Modernization (Bento Apple HIG v2.0 & Sheet XXL) | **SELESAI** | 0 native dialogs (`AppAlert`), Modal Sheet XXL 2-kolom, Aspect Ratio Inspector |
| **Fase 4** | Guardrail Sadar Konteks 20 Sektor Industri & Quiet Hours | **SELESAI** | Guardrail Farmasi, Otomotif, Salon, F&B, Agency, Retail, & Quiet Hours 22-06 WIB |
| **Fase 5** | Pengujian Otomatis Lolos 100%, Verifikasi & Hardening | **SELESAI** | 69 Tests, 399 Assertions, 0 Failure, 0 Error, Kode Produksi Bersih |

