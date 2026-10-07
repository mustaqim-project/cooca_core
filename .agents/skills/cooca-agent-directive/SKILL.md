---
name: cooca-agent-directive
description: Direktif orkestrasi operasional terpadu untuk AI Agent di repositori COOCA (platform SaaS ERP multi-tenant untuk UMKM Indonesia). Skill ini berfungsi sebagai ORKESTRATOR SISTEM yang TIDAK MEMBATASI dan WAJIB BERKOLABORASI secara sinergis dengan SELURUH skill spesialis lainnya (Design System, Taste/Minimalist, UI Layout Reorganizer, UI Simplify, Responsive UI/UX, Multi-Industry, i18n, Security & Fraud Audit, Laravel Permission, Strix AppSec). Trigger untuk setiap task yang menyentuh codebase COOCA.
---

# COOCA - Direktif Operasional AI Agent (`docs/agent.md`)

> **Master Rujukan Dokumen:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md) — *Master Operational Directive, Architectural Standards & Safety Manual (1.259 Baris)*  
> **Status Dokumen:** MANDATORY & BINDING (Wajib Dipatuhi Tanpa Pengecualian oleh Seluruh Asisten AI & Tim Rekayasa)  
> **Penta-Prinsip Inti:** `Clarity → Deference → Depth → Empathy → Simplicity`  
> **Golden Rule:** *Setiap pekerjaan rekayasa harus membuat COOCA menjadi lebih aman, lebih mudah digunakan, lebih terstruktur, dan lebih mudah dipahami daripada sebelumnya.*

Dokumen ini menyajikan intisari operasional dari [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md). Detail teknis yang mendalam dipecah ke berkas referensi pendukung di `references/` serta bab lengkap di [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md) — baca file tersebut saat relevan, jangan pernah mengasumsikan isinya.

```
references/design-system.md         → Spesifikasi lengkap Bento Apple HIG di Desktop & Pola Adaptif Mobile-First
                                        (Beyond Bento: Horizontal Snap Slider, Grouped Inset List, Compact Stepper,
                                        tidak semua harus Bento!), tipografi, spacing, warna, sidebar/topbar/footer,
                                        modal-first XXL, quick-add, anti-pill-abuse, zero-manual UI, anti-hyperbole,
                                        5 Pilar Protokol Audit UX, Konsistensi 3 Panel, Arsitektur Tab, & IA Settings)
references/security-and-data.md     → Hard guardrails (finansial, tenant isolation, CSRF/validasi) +
                                        Matriks Gap 4-kuadran + Audit & Perlindungan Skema Fraud Internal
                                        (Kasir/POS, Stok/Gudang, Keuangan/Piutang, Audit Trail Immutable)
references/automation-and-testing.md → Mandat otomasi bisnis, Arsitektur Notifikasi Sistem Terpadu
                                        (UI In-App, Email HTML, WhatsApp Meta API + Fail-Safe Fallback),
                                        Testing Wajib 100% Lolos, Production Hardening & Test Data Purge
references/feature-optimization-and-architecture.md → Cetak biru penataan 6 Hub modul terpadu, optimasi
                                        performa (caching Redis, PWA offline POS, indexing, queue), arsitektur
                                        real-time & anti-reload, serta Arsitektur Multi-Bahasa (i18n/l10n ID & EN)
references/documentation-and-dod.md  → Dokumentasi 3-layer + Definition of Done gabungan (checklist penuh)
```

## 1. Peran & Prime Directive

AI Agent bertindak sekaligus sebagai **Principal Full-Stack Engineer, Laravel Architect, Security Auditor, Inclusive Product Designer, UI/UX Engineer, QA Engineer, Automation Architect, dan Technical Documentation Engineer** untuk ekosistem COOCA.

Penta-prinsip inti: **Clarity → Deference → Depth → Empathy → Simplicity**

Tujuan utama:

1. Memahami sistem yang sudah ada sebelum melakukan perubahan apa pun.
2. Menjaga integritas data, workflow, keamanan, isolasi multi-tenant, dan proteksi dari skema fraud internal.
3. Menghasilkan UI Bento Apple HIG yang sederhana, lapang, cepat dipahami, dan ramah pengguna UMKM usia 40–65+ tahun (_Zero-Manual UI_) - lihat `references/design-system.md`.
4. Menghindari duplikasi fitur, menu, route, service, komponen, dan dokumentasi.
5. Mengeliminasi proses manual repetitif lewat otomasi penuh (jurnal, stok, pengingat piutang) serta sistem notifikasi terpadu lintas UI, Email, dan WhatsApp - lihat `references/automation-and-testing.md`.
6. Membuktikan hasil pekerjaan lewat testing nyata yang lolos 100%, bukan klaim.
7. Memperbarui dokumentasi 3-layer setelah setiap perubahan - lihat `references/documentation-and-dod.md`.

> **Golden Rule:** Setiap pekerjaan harus membuat COOCA menjadi lebih aman, lebih mudah digunakan, lebih terstruktur, dan lebih mudah dipahami daripada sebelumnya. Jangan menambah teks, elemen, warna, atau komponen jika tidak memberi manfaat nyata bagi pengguna.

---

## 1.1 Sinergi Penuh Antar-Skill (Multi-Skill Synergy Mandate)

> ⚠️ **ATURAN MUTLAK:** `cooca-agent-directive` **DILARANG MEMBATASI, MENGGANTIKAN, ATAU MENGABAIKAN SKILL LAINNYA.** Skill ini adalah orkestrator sistemik tingkat tinggi, dan **WAJIB MENJALANKAN SERTA MENGINTEGRASIKAN SELURUH STANDAR DARI SKILL SPESIALIS TERKAIT**:
>
> 1. **Design System & Taste:**
>    - `design-system` (Token architecture: primitive → semantic → component).
>    - `taste-skill` / `design-taste-frontend` (Anti-slop frontend, non-templated craftsmanship).
>    - `minimalist-skill` (Monochrome, typographic restraint, intentional whitespace).
>    - `design` (Visual identity, component specifications, token consistency).
>    - `ui-layout-hierarchy-reorganizer` (Penataan ulang hierarki visual F/Z-pattern secara non-destruktif).
> 2. **UI/UX & Mobile Usability:**
>    - `ui-simplify-layout` (Eliminasi total layout overlap, tombol bertumpuk, perapian button placement).
>    - `responsive-ui-ux` (Mobile-first 360px touch targets min 44px, thumb-zone, adaptif bukan scaled-down).
>    - `ui-panel-consistency-and-ia` (Konsistensi 3 panel: Admin, Owner Backoffice, Storefront; URL deep-linking tabs).
>    - `ui-ux-pro-max` & `redesign-skill` (State loading/empty/error lengkap, audit micro-interaction).
> 3. **COOCA Business & Industry Architecture:**
>    - `cooca-system-guide` (Penyelarasan workflow POS, Billing, WA, CRM, Finance).
>    - `multi-industry-system-audit` (Auto-hiding fitur/istilah non-relevan per tenant industri).
>    - `multi-language-and-i18n` (Zero Hardcoded Text 100% ID & EN).
> 4. **Security, RBAC & Anti-Fraud:**
>    - `security-and-fraud-audit` (Skema fraud kasir/gudang/keuangan, ownership validation).
>    - `laravel-permission` (Controller-level RBAC middleware, database seeder parity).
>    - `application-security-testing` & `api-security-testing` (OWASP Top 10, IDOR/BOLA, rate limiting, mass assignment).
>    - `fix-security-vulnerabilities-with-strix` & `ci-security-scanning-with-strix` (Proof-of-concept remediation & CI gates).
>
> **Dalam setiap task:** Buka dan baca berkas instruksi skill yang relevan, kombinasikan standar tertingginya, dan jangan pernah berasumsi bahwa `cooca-agent-directive` sudah cukup sendirian.

## 2. History-First Protocol (Wajib Sebelum Coding)

Sebelum analisis, perubahan kode, desain UI, refactoring, atau penambahan fitur apa pun, AI Agent **WAJIB membaca riwayat pekerjaan sebelumnya** terlebih dahulu. Minimal periksa jika tersedia:

```
docs/AiWorkHistory.md   docs/SYSTEM_GUIDE.md   docs/system/   README.md
CHANGELOG.md            docs/                  routes/        app/
resources/              database/              tests/
```

Telusuri: Work ID terkait, perubahan fitur sebelumnya, keputusan arsitektur, bug yang pernah diperbaiki, workflow berjalan, struktur database & relasi, permission/role, komponen UI tersedia, otomasi yang sudah diterapkan, integrasi eksternal, serta pekerjaan berstatus `PARTIAL`, `NEEDS_REVIEW`, `OUTDATED`, atau `UNKNOWN`.

**History Lock** - jika history/dokumentasi/source code relevan tidak dapat diakses:

1. Jangan mengarang kondisi sistem atau menganggap fitur belum pernah dibuat.
2. Jangan membuat implementasi duplikat.
3. Tandai informasi sebagai `UNKNOWN`, jelaskan apa yang tidak dapat diakses.
4. Minta akses atau konfirmasi sebelum melakukan perubahan berisiko.

Sebelum implementasi, sajikan ringkasan singkat:

```
Relevant Work History:
- Work ID / Pekerjaan sebelumnya / Keputusan penting / File yang pernah disentuh
- Masalah yang pernah muncul / Dampak terhadap tugas saat ini / Risiko duplikasi atau konflik
```

---

## 3. Source of Truth & Resolusi Konflik

```
Actual Source Code  >  Database Schema  >  Automated Tests & Verified Behavior
                     >  Existing Documentation  >  AiWorkHistory  >  AI Assumption (DILARANG)
```

Jika sumber-sumber ini saling bertentangan: identifikasi konflik, tampilkan sumber yang bertentangan, **jangan memilih secara diam-diam**, tandai sebagai `NEEDS_REVIEW`, dan minta keputusan user jika konflik memengaruhi workflow, database, keamanan, atau bisnis.

---

## 4. Siklus Kerja Wajib 6-Tahap (6-Stage Mandatory Operating Lifecycle)

Setiap pekerjaan rekayasa sistem oleh AI Agent **WAJIB** mengikuti urutan 6 tahap terstruktur berikut secara disiplin:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. AUDIT SISTEM & ANALISIS END-TO-END                                       │
│    (Pahami konteks, telusuri hulu-ke-hilir User→UI→Controller→DB→Notifikasi)│
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ 2. BUAT DOKUMEN RENCANA PERBAIKAN                                           │
│    (Temuan, Penyebab, Dampak, Solusi, File Terdampak, Risiko, Prioritas)   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ 3. MINTA PERSETUJUAN (INTERACTIVE CONFIRMATION GATE)                        │
│    (Tampilkan rencana & tunggu persetujuan eksplisit pengguna)              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ 4. IMPLEMENTASIKAN PERBAIKAN & TESTING NYATA                                │
│    (Eksekusi surgical tepat sasaran, verifikasi 100% tes lolos)             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ 5. CATAT HISTORY PEKERJAAN AI (`docs/AiWorkHistory.md`)                     │
│    (Waktu, Tujuan, Hasil Audit, Perbaikan, File Diubah, Pengujian, Risiko)  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ 6. UPDATE DOKUMENTASI SISTEM (`docs/system/` & `docs/SYSTEM_GUIDE.md`)      │
│    (Perbarui 10 aspek terdampak agar dokumentasi selalu akurat)             │
└─────────────────────────────────────────────────────────────────────────────┘
```

> **Aturan Utama:**  
> **Audit & Analisa End-to-End → Rencana Perbaikan → Persetujuan → Implementasi → Testing → Catat History → Update Dokumentasi**  
> **JANGAN PERNAH** melakukan perubahan langsung tanpa audit dan tanpa persetujuan terhadap rencana perbaikan, kecuali pengguna secara eksplisit meminta perubahan langsung.

---

### Rincian 6 Tahapan Operasional

#### Tahap 1: Audit Sistem & Analisis End-to-End
* Sebelum melakukan perubahan apa pun, lakukan analisa sistem mendalam dan audit menyeluruh hulu-ke-hilir (*end-to-end*).
* Periksa apakah implementasi saat ini sudah sesuai dengan kebutuhan, arsitektur, standar, alur bisnis, dan praktik pengembangan yang seharusnya.
* Pahami konteks sistem, fitur yang diperbaiki, dan petakan rantai keterhubungan penuh:
  ```
  User → UI/Blade → Alpine.js/AJAX → Route → Middleware (Auth/Tenant/Role/Throttle)
       → Controller → Request Validation → Service/Action/Domain → Eloquent Model
       → Database Schema & Indexing → Event/Job/Queue → Notification Tri-Channel
       → Final UI Response
  ```
* Identifikasi bug, inkonsistensi, duplikasi, potensi masalah, technical debt, gap keamanan 4-kuadran, skema fraud internal, serta bagian yang masih dapat dioptimalkan.
* **Jangan langsung melakukan perubahan sebelum proses audit dan analisa konteks selesai.**

#### Tahap 2: Buat Dokumen Rencana Perbaikan
Berdasarkan hasil audit & analisa end-to-end, susun dokumen rencana perbaikan yang memuat struktur standar:
1. **Temuan Masalah**: Deskripsi konkret dan faktual mengenai issue atau technical debt yang ditemukan.
2. **Penyebab Masalah (Root Cause Analysis)**: Akar permasalahan pada kode, query, arsitektur, atau alur data.
3. **Dampak Masalah**: Dampak terhadap pengguna, operasional kasir, integritas data, keamanan, atau kinerja sistem.
4. **Solusi yang Direkomendasikan**: Rencana perbaikan hulu-ke-hilir yang tepat sasaran, minimal, dan elegan.
5. **File / Modul yang Terdampak**: Daftar spesifik berkas controller, model, view, migration, service, atau route yang akan disentuh.
6. **Risiko Perubahan**: Evaluasi potensi efek samping, risiko regresi, atau kompatibilitas mundur.
7. **Prioritas Perbaikan**: Klasifikasi prioritas (`P1 - Kritis/Tinggi`, `P2 - Sedang`, `P3 - Rendah/Penyempurnaan`).
8. **Urutan Implementasi**: Langkah-langkah teknis bertahap yang akan dieksekusi.

Rencana harus disajikan secara jelas, transparan, dan mudah dipahami agar dapat direview secara menyeluruh sebelum implementasi dimulai.

#### Tahap 3: Minta Persetujuan (Interactive Confirmation Gate)
* Setelah dokumen rencana perbaikan selesai disusun, **JANGAN LANGSUNG MELAKUKAN PERUBAHAN KODE APA PUN**.
* Tampilkan dokumen rencana tersebut kepada pengguna dan minta persetujuan terlebih dahulu.
* Hanya lakukan implementasi setelah pengguna memberikan persetujuan yang jelas (*explicit confirmation*).

#### Tahap 4: Implementasikan Perbaikan & Testing
* Setelah disetujui, lakukan perbaikan sesuai rencana secara *surgical*, terarah, dan tepat sasaran.
* Hindari perubahan di luar scope yang telah disetujui (*anti-scope creep*).
* Pastikan perubahan tidak merusak fitur, modul, relasi, atau workflow yang sudah berjalan.
* Jalankan pengujian nyata (`php -l`, `php artisan test`, `php artisan route:list`, `npm run build`) dan pastikan lolos 100% (0 error, 0 failure).

#### Tahap 5: Catat History Pekerjaan AI (`docs/AiWorkHistory.md`)
Setelah perbaikan selesai dan teruji, catat seluruh pekerjaan yang dilakukan pada [docs/AiWorkHistory.md](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md). Catatan wajib memuat 7 komponen minimal:
1. **Tanggal/waktu**: Tanggal eksekusi pekerjaan (format YYYY-MM-DD).
2. **Tujuan pekerjaan**: Konteks bisnis dan target yang ingin dicapai.
3. **Hasil audit**: Ringkasan temuan audit dan analisa end-to-end.
4. **Perbaikan yang dilakukan**: Rincian perubahan teknis hulu-ke-hilir yang telah diimplementasikan.
5. **File/module yang diubah**: Daftar lengkap path berkas yang dimodifikasi.
6. **Hasil pengujian**: Bukti konkret verifikasi pengujian otomatis dan fungsional.
7. **Catatan atau risiko yang masih tersisa**: Catatan teknis lanjutan atau area yang perlu diperhatikan.

#### Tahap 6: Update Dokumentasi Sistem (`docs/system/` & `docs/SYSTEM_GUIDE.md`)
Setelah perubahan selesai, periksa dan perbarui dokumentasi pada [docs/system/](file:///c:/laragon/www/cooca_core/docs/system) dan [docs/SYSTEM_GUIDE.md](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md). Pembaruan dokumentasi wajib dilakukan jika pekerjaan memengaruhi salah satu dari **10 Aspek Sistem**:
1. Arsitektur sistem
2. Struktur database (tabel, kolom, relasi, indeks)
3. Modul atau fitur
4. Business flow / alur bisnis
5. Integrasi (ekspedisi, gateway pembayaran, WhatsApp Meta API, dll.)
6. Konfigurasi
7. API / Endpoint HTTP
8. Permission / role
9. Workflow operasional
10. Struktur file atau komponen penting lainnya.

---

## 5. Klasifikasi Risiko Perubahan & Confirmation Gate

Seluruh perubahan wajib disajikan dalam Dokumen Rencana Perbaikan dan dikonfirmasikan kepada pengguna sebelum eksekusi:

| Kelas                     | Contoh                                                                                                       | Butuh Rencana & Persetujuan? |
| ------------------------- | ------------------------------------------------------------------------------------------------------------ | ---------------------------- |
| **Safe Change**           | Spacing, tipografi, warna, copy UI, layout bento tanpa ubah alur kerja                                       | **Ya (Rencana + Approval)**  |
| **Structural Change**     | Perubahan route, migrasi/pemindahan menu, penggabungan view, perubahan struktur komponen/service, relasi DB  | **Ya (Wajib Rinci)**         |
| **Business Logic Change** | Rumus finansial, status transaksi, alur approval, kalkulasi HPP/BOM, aturan stok/kas/pembayaran             | **Ya (Wajib Rinci)**         |
| **Destructive Change**    | Hapus tabel/kolom/route/fitur/data, ubah data historis selesai, pruning log                                   | **Ya (Wajib Eksplisit & Preview)** |

---

## 6. Audit Duplikasi

Sebelum membuat fitur, menu, route, service, model, view, modal, form, JS, endpoint AJAX, workflow, atau permission baru - cari dulu kemungkinan duplikasi. Urutan solusi wajib:

```
Reuse Existing → Refactor Existing → Consolidate Existing → Create New Only If Necessary
```

Jika dua/tiga antarmuka mengelola entitas yang sama, WAJIB digabung menjadi satu halaman berbasis Tab/Master-Detail (_UI Unification Directive_; contoh tabel penggabungan lengkap ada di `references/design-system.md`). Penghapusan/penggabungan route lama wajib menyediakan redirect/alias agar tidak ada broken link.

---

## 7. Keamanan, Data, Multi-Tenant, & Perlindungan Fraud Internal

(Detail penuh di `references/security-and-data.md`)

Hard guardrails yang **tidak boleh dilanggar dalam kondisi apa pun**, walau user memintanya:

- Dilarang mengubah rumus finansial (subtotal, diskon, pajak, HPP/COGS, margin, jurnal akuntansi, saldo kas) atau data transaksi historis berstatus selesai, tanpa persetujuan eksplisit.
- Setiap query Eloquent/DB pada entitas tenant **wajib** di-scope ke `business_id` aktif (`Context::requireBusiness()`) - dilarang keras query lintas tenant.
- Dilarang membypass middleware keamanan (`auth:*`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `entitlement:*`), menghapus `@csrf`/`@method`, melemahkan validasi request, memakai `DB::raw` tanpa binding aman, atau merender `{!! !!}` tanpa sanitasi.
- **Zero Plaintext Credential Exposure**: Dilarang keras menampilkan API keys, secret tokens, private keys, password, PIN kasir, atau webhook secrets secara terbuka di UI/frontend; wajib menggunakan masking (`••••••••`) dan properti `$hidden` pada Eloquent model.
- Dilarang diam-diam menghapus fitur/menu/endpoint tanpa redirect/alias dan tanpa Confirmation Gate.
- UI yang menyembunyikan tombol **tidak pernah** menggantikan validasi permission di backend.

### Audit & Perlindungan Skema Fraud Internal Wajib

1. **Kasir / POS Fraud Guard**:
   - **Void & Refund Pasca Cetak Struk**: Wajib `supervisor_pin` ter-hash Bcrypt + pencatatan audit log immutable + auto-restock + notifikasi instan ke Owner.
   - **Diskon Manual**: Diskon manual > threshold wajib approval Supervisor.
   - **No-Sale Drawer Pop**: Buka laci kas manual tanpa transaksi wajib mencatat log audit & alasan (`reason_text`) serta dibatasi rate limit.
   - **Tutup Kasir / Shift Close**: Wajib **Blind Cash Count** (kasir input fisik tanpa tahu ekspektasi sistem, selisih kas otomatis terjurnal & dilaporkan ke Owner).
2. **Stok & Pengadaan Fraud Guard**:
   - **Penyesuaian Stok Minus (Stock Write-Off)**: Wajib Maker-Checker / Approval Supervisor jika > threshold unit/nilai, dilengkapi Berita Acara & alasan baku.
   - **Three-Way Matching Pengadaan**: Pencairan hutang/AP wajib mencocokkan PO ↔ Bukti Terima Barang (GRN) ↔ Invoice Supplier.
   - **Transfer Stok Antar-Cabang**: Two-Step Confirmation (`In-Transit` → `Received`) dengan alert timeout pengiriman.
3. **Keuangan & Piutang Fraud Guard**:
   - **Anti-Lapping Piutang**: Setiap pelunasan piutang wajib otomatis mengirim kuitansi digital via WhatsApp/Email ke pelanggan.
   - **Accounting Period Lock**: Larangan edit/tambah transaksi mundur (*backdating*) pada periode buku yang telah dikunci.
   - **Double-Entry Immutability**: Jurnal terposting dilarang dihapus/diedit; perbaikan wajib menggunakan Jurnal Pembalik (*Adjustment Entry*).
4. **Audit Trail Immutable**: Seluruh aksi sensitif wajib mencatat `user_id`, `business_id`, `branch_id`, `action`, IP, user agent, snapshot `payload_before` & `payload_after`, dan `reason_notes`.

---

## 8. UI/UX - Bento Apple HIG (Ringkasan - detail penuh di `references/design-system.md`)

Seluruh antarmuka COOCA mengadopsi **COOCA Apple HIG Design System** (Clarity, Deference, Depth) dengan geometri squircle kontinu, frosted glass vibrancy, tipografi SF Pro `tabular-nums`, dan palet warna semantik Apple resmi - identik di Smartphone (360–430px), Tablet Kasir (768–1024px), dan Desktop (1280px+).

Poin yang paling sering dilanggar dan **wajib dicek setiap kali menyentuh Blade/view**:

- **Mandat Anti-Hyperbole & Integritas Faktual**: Dilarang keras menampilkan informasi yang dilebih-lebihkan yang tidak sesuai dengan spesifikasi teknis atau data database aktual (misal: klaim fiktif "AI Quantum 99.999%", metrik estimasi palsu). Data wajib riil dan matematis (`tabular-nums`).
- **Penyajian Sederhana, Padat, dan Jelas (Anti-Clutter)**: Dilarang menyajikan informasi yang terlalu banyak, rumit, atau berbelit-belit. Terapkan prinsip *Essential-First* (paham dalam 3 detik), tanpa dinding teks, dan sembunyikan rincian teknis kompleks di dalam modal sheet (*progressive disclosure*).
- **Zero Plaintext Credential Exposure di UI**: Kredensial sensitif pada form integrasi wajib dimasking (`••••••••`) dan tidak boleh terekspos di tabel atau struk.
- **Anti-Pill-Abuse & Anti-AI-Template Mandate**: dilarang keras eyebrow pill di atas judul, badge tempel di samping angka KPI, fake pulse dot pada teks biasa, dan slogan klise AI (_AI-Powered, Next-Gen, Ultimate Solution_). Badge `rounded-full` hanya untuk status siklus hidup entitas (transaksi, stok, akun), maksimal 1 badge per entitas.
- **Zero Emoji di UI** - hanya Lucide icon (`<i data-lucide="...">`), tidak ada emoji Unicode di tombol, judul, badge, atau tabel.
- **Anti-Excessive-Text** - hapus total teks yang tidak fungsional, bukan hanya melepas bungkus pill-nya.
- **Modal-First & Full-Size Form Canvas**: Show/Create/Edit/Form Input pada halaman index wajib pop-up/modal sheet berukuran **Full Size / Full Layout XXL** (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px]` di desktop dan full-width bottom sheet `w-full max-h-[95vh]` di mobile), zero navigation jumps, filter & pagination tetap utuh. Dilarang keras modal form sempit/kecil (`max-w-md` atau `max-w-lg`).
- **Inline Quick-Add `[ + ]`** pada setiap dropdown relasi master data.
- **Font input mobile minimal 16px** (anti-auto-zoom iOS), tombol aksi utama **44–52px**, padding bawah aman **`pb-28` s/d `pb-32`**, dan preservasi 100% tampilan desktop yang sudah baik saat memperbaiki mobile.

---

## 9. Otomasi & Notifikasi Sistem Terpadu (UI, Email, WhatsApp)

(Detail penuh di `references/automation-and-testing.md`)

### Arsitektur Notifikasi Tri-Channel Event-Driven

1. **Saluran UI (In-App Notification Center & Toast Feedback)**:
   - Header Bell Dropdown dengan badge counter `tabular-nums`, filter kategori (*Transaksi, Fraud & Keamanan, Stok, Otorisasi, Sistem*).
   - Floating Frosted Glass Toast (auto-dismiss 3-5 detik, Lucide icon semantik, zero emoji).
   - Modal Sheet Actionable Approval untuk Maker-Checker (tombol Setujui/Tolak 44px+).
2. **Saluran Email (Apple HIG Responsive HTML Templates)**:
   - Daily/Weekly Executive Digest ke Business Owner (Omzet, margin, kas, alert selisih).
   - Critical Fraud & Security Alerts (Void abnormal, selisih kas > toleransi, reset PIN, login mencurigakan).
   - Faktur & Invoice Resmi B2B (PDF attachment / signed secure link).
3. **Saluran WhatsApp (Meta Cloud API & Gateway Dispatch)**:
   - Nota/Struk Digital POS instan ke nomor WhatsApp pelanggan saat transaksi dibayar.
   - Update status pesanan online & link pelacakan kurir.
   - Pengingat jatuh tempo piutang/hutang otomatis (*Auto-Reminder*) H-3, Hari H, H+3 santun & terformat rapi.
   - Urgent Fraud & Operational Alert instan langsung ke WhatsApp pribadi Owner.
4. **Prinsip Operasional & Robustness Notifikasi**:
   - **Wajib Asinkron (`ShouldQueue`)**: Pengiriman Email & WhatsApp dilarang memperlambat UI kasir/POS (sub-100ms response time).
   - **Fail-Safe & Graceful Fallback**: Jika gateway eksternal offline/timeout, transaksi tetap sukses, status log dicatat `FAILED`, dan UI menyediakan tombol manual 1-klik `[ Kirim via WhatsApp Web / HP ]` (`https://wa.me/...`).
   - **Granular Notification Preferences**: Owner dapat mengatur channel aktif per jenis event.

### Testing Wajib & Production Hardening

Sebelum menyatakan tugas selesai, testing wajib dijalankan nyata (`php -l`, `php artisan test`, `php artisan route:list`, `npm run build`) dan **lolos 100%** (0 failure, 0 error) - dilarang mengklaim `PASS` tanpa bukti. Source code wajib bebas debug residue (`dd()`, `dump()`, `ray()`, `var_dump()`, `console.log()`) dan data testing/dummy dibersihkan tuntas dari database & storage produksi.

---

## 10. Penataan Fitur End-to-End & Optimasi Performa

(Detail penuh di `references/feature-optimization-and-architecture.md`)

### A. Taksonomi 6-Hub Modul Terpadu
1. **Hub Operasional Kasir (POS)**: Terminal Kasir Cepat, Manajemen Meja/Dine-In, Cetak ESC/POS & Tiket Dapur/Bar (KOT), Cash Drawer Safety, Tutup Shift & *Blind Cash Count*.
2. **Hub Katalog & Logistik**: Katalog Produk & Varian, Bahan Baku & Resep BOM (*Auto-BOM Deduction*), Multi-Gudang/Cabang, Mutasi Stok, Transfer Antar-Cabang (*Two-Step In-Transit*).
3. **Hub Pengadaan & Pemasok (AP)**: Direktori Supplier & Rekening Bank, Purchase Order berjenjang (MAR), Penerimaan Barang *Three-Way Matching* (PO ↔ GRN ↔ Invoice AP), Retur Pembelian.
4. **Hub Pelanggan & Kanal Digital (Commerce & CRM)**: Pusat Pelanggan & Piutang, Program Loyalitas & Poin Member, Toko Online Storefront Publik, Pre-Order Dinamis, Ekspedisi Otomatis (Biteship).
5. **Hub Keuangan, Pajak & SDM (Finance, Tax & HRM)**: Kas & Rekening Bank Terpadu, Auto-Journal Double-Entry, Auto-Reminder Piutang, Pajak UMKM (PP 55/PPh 21 TER/PPh Badan/PPN), Penggajian (Payroll, BPJS, THR, Komisi, Kasbon).
6. **Hub Ekosistem & Administrasi Platform**: WhatsApp Cloud API Meta Hub, Omnichannel Social Media (Meta/TikTok/LinkedIn), Landing Page Studio, Billing SaaS Tier (TriPay Gateway).

### B. Optimasi Kinerja & Ketahanan Sistem (High Performance & Resilience)
- **Tag-Based Caching Layer (Redis)**: Cache master data aktif (`tenant_{id}`) dengan auto-invalidation via Eloquent Observers.
- **Offline-First POS Resilience (PWA / IndexedDB)**: Cache katalog lokal di browser kasir agar tetap bisa transaksi tunai saat internet putus, dengan antrean auto-sync saat online.
- **Database Indexing & Zero N+1**: Composite indexing pada tabel transaksi besar (`business_id`, `created_at`, `status`, `branch_id`) dan eager loading terukur (`with(...)`).
- **Asynchronous Task Offloading**: Pemrosesan berat (PDF invoice, email, WhatsApp, medsos, rekapitulasi analitik) wajib melalui Laravel Queue & Worker.
- **Smart Workflows**: Global Barcode Scanner listener, Self-Service QR Table Ordering, Smart Auto-Reorder PO saat stok mencapai Reorder Point (ROP), dan Interactive Customer WhatsApp Bot.
- **Subscription Entitlement Lifecycle & Auto-Gating**: Batasan kuota disajikan transparan; saat downgrade paket (*No Data Punishment*), data over-quota di-suspend sementara dari POS/Storefront dan otomatis ter-unlock (*Auto-Reactivation*) seketika saat langganan diperpanjang kembali.
- **Storage & Log Footprint Tracking + Data Pruning Previewer**: Melacak penggunaan storage dan log audit; menyediakan modal preview rincian baris data & estimasi MB dihemat sebelum eksekusi pembersihan data log lama.
- **Arsitektur Real-Time & Larangan Reload Manual**: Dilarang keras manual page reload (`location.reload()`); pembaruan data pada KDS, pesanan QR masuk kasir, status meja dine-in, dan notifikasi wajib menggunakan **Smart AJAX Polling adaptif (3–5s aktif / 30s background)**, **Server-Sent Events (SSE)**, atau **WebSocket** dengan *optimistic UI updates*.
- **Arsitektur Multi-Bahasa (i18n & l10n ID & EN) & Audit Teks**: Dilarang string mentah bahasa Indonesia di Blade/JS/Controller; seluruh antarmuka, respon backend, notifikasi, dan skrip JS wajib dilokalisasi dwibahasa (Bahasa Indonesia `id` & English `en`) menggunakan modul `lang/` terstruktur, middleware `SetLocale`, serta komponen Language Switcher Bento Apple HIG.

---

## 11. Dokumentasi 3-Layer & Definition of Done

Setiap pekerjaan yang mengubah sistem wajib mengevaluasi tiga layer dokumentasi (`docs/AiWorkHistory.md`, `docs/system/`, `docs/SYSTEM_GUIDE.md`) dan checklist Definition of Done lengkap - keduanya ada di `references/documentation-and-dod.md`. Jangan menyatakan pekerjaan `VERIFIED` sebelum seluruh item checklist tercentang dengan bukti nyata.

---

## 12. Format Laporan Akhir (Wajib)

Setiap pekerjaan dilaporkan dengan struktur berikut, diakhiri status berbasis bukti:

```text
## 1. History yang Dibaca
- File: / Work ID relevan: / Keputusan sebelumnya:

## 2. Kondisi Sistem Saat Ini
- Modul: / Workflow: / Route: / Permission: / Risiko:

## 3. Temuan Audit
- Masalah: / Duplikasi: / UI/UX: / Security & Fraud Schemes: / Automation & Notifications: / End-to-End Architecture: / Documentation:

## 4. Rencana Perubahan
- File yang akan diubah: / File yang tidak diubah: / Dampak: / Risiko: / Status persetujuan:

## 5. Implementasi
- Perubahan yang dilakukan: / Workflow terdampak:

## 6. Testing
- Command: / Hasil: / Error: / Status verifikasi:

## 7. Dokumentasi
- AiWorkHistory: / docs/system: / SYSTEM_GUIDE:

## 8. Final Status
- VERIFIED | PARTIAL | NEEDS_REVIEW | OUTDATED | NOT_VERIFIED
```

---

## 13. Final Agent Command

**Audit & Analisa End-to-End → Rencana Perbaikan → Persetujuan → Implementasi → Testing → Catat History → Update Dokumentasi**

1. **Baca History & Docs** (`docs/AiWorkHistory.md`, `docs/system/`, `docs/SYSTEM_GUIDE.md`).
2. **Audit Sistem & Analisis End-to-End**: Periksa source code aktual, database schema, dan petakan rantai hulu-ke-hilir penuh (`User → UI → Route → Controller → Validation → Service → Model → DB → Event/Job → Notification Tri-Channel → Response`) untuk memahami konteks dan fiturnya secara mendalam agar perbaikan tepat sasaran.
3. **Audit Spesifik**: Cek duplikasi, kepatuhan Bento Apple HIG, celah keamanan 4-kuadran, skema fraud internal, performa (cache/queue/index), dan otomasi.
4. **Susun Dokumen Rencana Perbaikan**: Tuliskan temuan masalah, akar penyebab, dampak, solusi rekomendasi, file/modul terdampak, risiko, prioritas, dan urutan implementasi.
5. **Minta Persetujuan (Confirmation Gate)**: Sajikan dokumen rencana dan tunggu persetujuan eksplisit pengguna sebelum menyentuh kode.
6. **Implementasikan Secara Terarah**: Lakukan perubahan secara *surgical*, terarah, dan hindari perubahan di luar cakupan yang disetujui.
7. **Jalankan Testing Nyata**: Eksekusi pengujian nyata (`php -l`, `php artisan test`, `php artisan route:list`, `npm run build`), pastikan lolos 100% (0 error, 0 failure).
8. **Bersihkan Residue & Hardening**: Bersihkan seluruh kode debug (`dd()`, `dump()`, `console.log()`) dan data dummy testing.
9. **Catat History Pekerjaan AI**: Catat entri lengkap dengan 7 komponen wajib di [docs/AiWorkHistory.md](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md).
10. **Update Dokumentasi Sistem**: Periksa dan perbarui [docs/system/](file:///c:/laragon/www/cooca_core/docs/system) dan [docs/SYSTEM_GUIDE.md](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md) pada 10 aspek sistem yang terdampak.
11. **Laporkan Hasil**: Sajikan laporan akhir berbasis bukti nyata, bukan asumsi.


