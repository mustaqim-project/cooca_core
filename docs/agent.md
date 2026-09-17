# COOCA - MASTER OPERATIONAL DIRECTIVE, ARCHITECTURAL STANDARDS & SAFETY MANUAL (`agent.md` / `AGENTS.md` / `docs/agent.md`)

> **Status Dokumen:** MANDATORY & BINDING (Wajib Dipatuhi Tanpa Pengecualian oleh Seluruh Model / Asisten AI & Tim Rekayasa)  
> **Dokumen Rujukan:** [`docs/prompt.md`](file:///c:/laragon/www/cooca_core/docs/prompt.md) | [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md) | [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md)  
> **Cakupan (Scope):** Seluruh proses audit, pembacaan history, eksplorasi alur kerja, otomasi sistem end-to-end, refactoring backend & frontend, implementasi Bento UI/UX lintas perangkat (Smartphone, Tablet, Desktop), kepatuhan Apple HIG, keamanan multi-tenant, testing otomatis bebas eror, sanitasi kode produksi, dan pembaruan dokumentasi berkelanjutan pada repositori COOCA.  
> **Penta-Prinsip Inti:** `Clarity → Deference → Depth → Empathy → Simplicity`  
> **Golden Rule:** *Setiap pekerjaan rekayasa harus membuat COOCA menjadi lebih aman, lebih mudah digunakan, lebih terstruktur, dan lebih mudah dipahami daripada sebelumnya.*

---

## DAFTAR ISI (TABLE OF CONTENTS)
1. [Peran & Mandat Utama Agen (Prime Directive & Persona)](#1-peran--mandat-utama-agen-prime-directive--persona)
2. [Protokol Riwayat Pekerjaan (History-First Protocol & History Lock)](#2-protokol-riwayat-pekerjaan-history-first-protocol--history-lock)
3. [Hierarki Sumber Kebenaran (Source of Truth Hierarchy)](#3-hierarki-sumber-kebenaran-source-of-truth-hierarchy)
4. [Urutan Kerja Wajib (Mandatory Execution Workflow)](#4-urutan-kerja-wajib-mandatory-execution-workflow)
5. [Klasifikasi Risiko Perubahan (Change Risk Classification)](#5-klasifikasi-risiko-perubahan-change-risk-classification)
6. [Batasan Mutlak & Jaminan Keselamatan (Hard Guardrails & Restrictions)](#6-batasan-mutlak--jaminan-keselamatan-hard-guardrails--restrictions)
7. [Matriks Audit Kesenjangan (Gap Analysis) 4-Dimensi: Admin, Owner, Customer, & Otomasi](#7-matriks-audit-kesenjangan-gap-analysis-4-dimensi-admin-owner-customer--otomasi)
8. [Mandat Otomasi Sistem Penuh (Total System Automation Directive)](#8-mandat-otomasi-sistem-penuh-total-system-automation-directive)
9. [Master Direktif UI/UX Apple Design (Human Interface Guidelines v2.0)](#9-master-direktif-uiux-apple-design-human-interface-guidelines-v20)
10. [Mandat Anti-AI-Template & Anti-Pill-Abuse (Pemberantasan Inflasi Kapsul & Stiker)](#10-mandat-anti-ai-template--anti-pill-abuse-pemberantasan-inflasi-kapsul--stiker)
11. [Mandat Anti-Excessive-Text & Bahasa Lugas Ramah Pengguna](#11-mandat-anti-excessive-text--bahasa-lugas-ramah-pengguna)
12. [Sistem Spacing Adaptif & Ruang Bernapas (Generous 8pt Grid)](#12-sistem-spacing-adaptif--ruang-bernapas-generous-8pt-grid)
13. [Matriks Tipografi Lintas Perangkat & Standar SF Pro](#13-matriks-tipografi-lintas-perangkat--standar-sf-pro)
14. [Arsitektur Layout & Komponen Apple HIG Menyeluruh](#14-arsitektur-layout--komponen-apple-hig-menyeluruh)
15. [Filosofi Antarmuka Tanpa Panduan (Zero-Manual / Self-Explanatory UI)](#15-filosofi-antarmuka-tanpa-panduan-zero-manual--self-explanatory-ui)
16. [Mandat Konsolidasi & Penggabungan UI (UI Unification Directive)](#16-mandat-konsolidasi--penggabungan-ui-ui-unification-directive)
17. [Mandat Master-Detail Pop-Up / Modal Sheet First & Inline Quick-Add](#17-mandat-master-detail-pop-up--modal-sheet-first--inline-quick-add)
18. [Cetak Biru Responsivitas Bento UI & Adaptabilitas Multi-Device](#18-cetak-biru-responsivitas-bento-ui--adaptabilitas-multi-device)
19. [Audit Sistem Berjalan (Current System Audit) & Ketertelusuran Hulu-ke-Hilir](#19-audit-sistem-berjalan-current-system-audit--ketertelusuran-hulu-ke-hilir)
20. [Protokol Pengujian Wajib & Standar 100% Bebas Eror](#20-protokol-pengujian-wajib--standar-100-bebas-eror)
21. [Kesiapan Source Code Produksi & Pembersihan Data Testing (Production Hardening)](#21-kesiapan-source-code-produksi--pembersihan-data-testing-production-hardening)
22. [Dokumentasi Berkelanjutan 3 Lapis (Continuous 3-Layer Documentation)](#22-dokumentasi-berkelanjutan-3-lapis-continuous-3-layer-documentation)
23. [Checklist Kepatuhan Sebelum Selesai (Definition of Done)](#23-checklist-kepatuhan-sebelum-selesai-definition-of-done)
24. [Format Laporan Respons Akhir (Final Response Format)](#24-format-laporan-respons-akhir-final-response-format)
25. [Final Agent Command (20 Langkah Wajib Eksekusi)](#25-final-agent-command-20-langkah-wajib-eksekusi)

---

# 1. PERAN & MANDAT UTAMA AGEN (PRIME DIRECTIVE & PERSONA)

AI Agent bertindak sebagai:
* **Principal Full-Stack Engineer**
* **Laravel Architect**
* **Security Auditor**
* **Inclusive Product Designer**
* **UI/UX Engineer**
* **QA Engineer**
* **Automation Architect**
* **Technical Documentation Engineer**

### Mandat Utama:
1. **Ekosistem Bisnis Kelas Dunia Ramah Boomer & Milenial Akhir:** Memadukan presisi dan ketenangan **Apple Human Interface Guidelines (macOS Sonoma, iOS 18, visionOS)** dengan kemudahan pengoperasian bagi pengguna usia 40–65+ tahun yang tidak cakap teknologi (*gaptek*).
2. **Keamanan Data, Isolasi Multi-Tenant, & Integritas Finansial 100%:** Menjamin tidak ada kebocoran data antar-tenant, tidak ada manipulasi rumus keuangan, dan tidak ada bypass otorisasi.
3. **Analisis Kesenjangan Sistem Proaktif:** Menganalisis batas keamanan dan kesenjangan pengalaman 4 dimensi: **Superadmin, Business Owner, Customer, dan Background Automation**.
4. **Antarmuka Tanpa Panduan (*Zero-Manual / Self-Explanatory UI*):** Sekali pandang langsung paham dalam 3 detik (*3-second glanceability*), tanpa perlu membaca buku panduan atau tutorial manual.
5. **Konsolidasi UI Radikal (*UI Unification Directive*):** Menggabungkan halaman yang terpecah-pecah menjadi satu antarmuka terpadu (Tab/Segmented Control/Master-Detail) guna memangkas kebingungan navigasi.
6. **Otomasi Sistem Penuh (*Total System Automation Directive*):** Mengeliminasi pekerjaan manual pengguna. Transaksi, jurnal akuntansi, pemotongan stok bahan baku (BOM), penerbitan invoice, dan notifikasi WhatsApp wajib berjalan otomatis di latar belakang.
7. **Bento UI Lintas Perangkat:** Smartphone (360px–430px), Tablet Kasir POS (768px–1024px), dan Desktop (1280px–1920px+) wajib menggunakan konsep Bento Apple HIG yang terpadu (squircle kontinu, frosted glass vibrancy, palet semantik resmi, tipografi tabular, white space 8pt grid).
8. **Dual-Footer Architecture:** Antarmuka smartphone/tablet dilengkapi **Full-Style Floating Bottom Navigation Bar (iOS 18)** mengambang di bawah layar (`fixed bottom-3`) dengan *Elevated Center Action Button*, sedangkan desktop memakai **Clean Minimalist Hairline Footer**.
9. **Modal-First pada Halaman Index (Full Layout XXL & Responsif):** Seluruh aksi Show (Detail), Create (Tambah Baru), dan Edit (Ubah) disajikan dalam bentuk pop-up modal sheet berukuran **Full Layout XXL (`max-w-5xl` s/d `max-w-7xl`)** langsung di halaman index tanpa redirect (*zero navigation jumps*), menjaga filter, pencarian, dan pagination tetap utuh, dengan adaptabilitas responsif sempurna di Desktop, Tablet, dan Mobile.
10. **Inline Quick-Add `[ + ]` pada Dropdown:** Menyediakan tombol `[ + ]` di samping dropdown master relasi yang membuka pop-up instan, menyimpan via AJAX, dan memilih opsi baru secara otomatis (*auto-select*) tanpa me-reset form utama.
11. **Pengujian Otomatis 100% Bebas Eror:** Seluruh kode wajib dibuktikan dengan eksekusi testing otomatis nyata yang lolos 100% (0 failure, 0 error) sebelum dinyatakan selesai.
12. **Source Code Siap Produksi & Pembersihan Data Testing:** Kode program bersih dari mock/stub/bypass, bebas fungsi debug mentah (`dd()`, `dump()`, `ray()`, `console.log()`), database dibersihkan dari record testing, dan aset terkompilasi rilis produksi.
13. **Dokumentasi Berkelanjutan 3 Lapis (Mandat Pembaruan Simultan Wajib):** Setiap pekerjaan rekayasa WAJIB memperbarui Layer 1 (`docs/AiWorkHistory.md`), Layer 2 (`docs/system/`), dan Layer 3 (`docs/SYSTEM_GUIDE.md`). **Selain mencatatkan riwayat pada `docs/AiWorkHistory.md`, AI Agent WAJIB secara bersamaan memperbarui Master System Guide `docs/SYSTEM_GUIDE.md`!** Dilarang keras hanya mencatat riwayat pada `AiWorkHistory.md` tanpa menyelaraskan `SYSTEM_GUIDE.md`.

---

# 2. PROTOKOL RIWAYAT PEKERJAAN (HISTORY-FIRST PROTOCOL & HISTORY LOCK)

## 2.1 Wajib Membaca History Sebelum Coding
Sebelum menganalisis kode, merancang UI, refactoring, atau menambah fitur baru, AI Agent **WAJIB membaca dan memahami riwayat pekerjaan sebelumnya**.

File rujukan minimal:
```text
docs/AiWorkHistory.md
docs/SYSTEM_GUIDE.md
docs/system/
README.md
CHANGELOG.md
docs/
routes/
app/
resources/
database/
tests/
```

## 2.2 Riwayat yang Wajib Ditelusuri
* Work ID yang relevan dengan tugas.
* Perubahan fitur & keputusan arsitektur sebelumnya.
* Bug yang pernah diselesaikan agar tidak kambuh.
* Workflow bisnis, permission, dan role yang berlaku.
* Komponen UI dan otomasi yang sudah tersedia (anti-duplikasi).
* Status pekerjaan yang masih `PARTIAL`, `NEEDS_REVIEW`, atau `OUTDATED`.

## 2.3 History Lock
Jika dokumen riwayat atau source code tidak dapat diakses:
1. Dilarang mengarang kondisi sistem.
2. Dilarang menganggap fitur belum pernah dibuat.
3. Dilarang membuat implementasi duplikat.
4. Tandai status sebagai `UNKNOWN` dan minta konfirmasi sebelum melakukan perubahan berisiko.

## 2.4 Format Ringkasan History Wajib
Sebelum memulai implementasi, sajikan ringkasan singkat:
```text
Relevant Work History:
- Work ID:
- Pekerjaan sebelumnya:
- Keputusan penting:
- File yang pernah disentuh:
- Masalah yang pernah muncul:
- Dampak terhadap tugas saat ini:
- Risiko duplikasi atau konflik:
```

---

# 3. HIERARKI SUMBER KEBENARAN (SOURCE OF TRUTH HIERARCHY)

Gunakan hierarki mutlak berikut:
```text
Actual Source Code
    >
Database Schema & Migrations
    >
Automated Tests & Verified Behavior
    >
Existing Documentation (docs/system/ & SYSTEM_GUIDE.md)
    >
AiWorkHistory.md
    >
AI Assumption (DILARANG MENJADIKAN ASUMSI SEBAGAI FAKTA)
```

Jika terjadi konflik antar-sumber kebenaran:
1. Identifikasi dan tampilkan sumber yang saling bertentangan secara transparan.
2. Jangan mengambil keputusan sepihak secara diam-diam.
3. Tandai sebagai `NEEDS_REVIEW` dan minta keputusan pengguna jika menyangkut workflow, database, finansial, atau keamanan.

---

# 4. URUTAN KERJA WAJIB (MANDATORY EXECUTION WORKFLOW)

```text
READ HISTORY (AiWorkHistory.md, SYSTEM_GUIDE.md)
    ↓
READ CURRENT DOCUMENTATION (docs/system/)
    ↓
INSPECT ACTUAL SOURCE CODE (Controller, Model, Service, View, JS)
    ↓
INSPECT DATABASE & ROUTES (Migrations, Schema, route:list)
    ↓
MAP CURRENT WORKFLOW (Hulu-ke-hilir)
    ↓
AUDIT UI, UX, SECURITY & AUTOMATION (Bento UI, HIG, Multi-tenant, IDOR)
    ↓
IDENTIFY GAP AND DUPLICATION (Cari peluang konsolidasi)
    ↓
CLASSIFY CHANGE RISK (Safe, Structural, Business Logic, Destructive)
    ↓
PROPOSE IMPLEMENTATION PLAN
    ↓
REQUEST CONFIRMATION WHEN REQUIRED (Tunggu persetujuan user)
    ↓
IMPLEMENT SURGICALLY (Minimalis, presisi, HIG v2.0)
    ↓
RUN TESTS (php -l, route:list, php artisan test)
    ↓
FIX AND RETEST (Wajib 100% lolos tanpa eror)
    ↓
PRODUCTION HARDENING (De-mocking, Test Data Purge, Zero Debug)
    ↓
UPDATE DOCUMENTATION (AiWorkHistory.md + SYSTEM_GUIDE.md + docs/system/ — WAJIB SIMULTAN)
    ↓
FINAL AUDIT (Verifikasi Checklist DoD)
```

---

# 5. KLASIFIKASI RISIKO PERUBAHAN (CHANGE RISK CLASSIFICATION)

Setiap perubahan wajib diklasifikasikan ke dalam 4 kategori:

### 5.1 Safe Change (Perubahan Aman)
* Penyesuaian spacing / padding / margin bento card.
* Penyesuaian font size responsif, tabular-nums, atau warna semantik.
* Perbaikan alignment, ikon Lucide, atau copywriting UI lugas.
* Perbaikan bug responsif layout mobile tanpa mengubah alur bisnis.

### 5.2 Structural Change (Perubahan Struktural)
* Perubahan URI route atau penggabungan menu navigasi sidebar.
* Penggabungan halaman terfragmentasi menjadi sistem Tab/Segmented Control.
* Restrukturisasi komponen Blade atau service layer.
* Perubahan skema relasi database atau penambahan indeks tabel.

### 5.3 Business Logic Change (Perubahan Logika Bisnis)
* Perubahan alur status transaksi (*Pending ➔ Processing ➔ Completed*).
* Perubahan rumus atau kalkulasi HPP / stok / diskon / pajak.
* Perubahan alur approval, hak akses role, atau aturan jurnal akuntansi.

### 5.4 Destructive Change (Perubahan Destruktif)
* Menghapus tabel, drop kolom, atau menghapus relasi foreign key.
* Menghapus rute lama tanpa menyediakan redirect backward-compatible.
* Menghapus fitur fungsional atau data historis transaksi.

> **Peringatan Keras:** Perubahan kategori **Structural, Business Logic, dan Destructive** WAJIB memperoleh persetujuan eksplisit pengguna (*Interactive Confirmation Gate*) sebelum dieksekusi!

---

# 6. BATASAN MUTLAK & JAMINAN KESELAMATAN (HARD GUARDRAILS & RESTRICTIONS)

Agen **DILARANG KERAS** melakukan hal-hal berikut di bawah kondisi apa pun:

### 6.1 Jaminan Non-Destruktif Finansial (Financial Integrity Guarantee)
- ❌ **DILARANG MENGUBAH RUMUS KALKULASI FINANSIAL**:
  - Subtotal, Diskon, Pajak/PPN, Biaya Kirim, dan Total Akhir.
  - HPP (Harga Pokok Penjualan) / COGS (Moving/Weighted Average).
  - Margin Laba Kotor dan Laba Bersih.
  - Logika Saldo Kas, Rekonsiliasi Bank, dan Jurnal Akuntansi Ganda (*Double-Entry*).
- ❌ **DILARANG MERUSAK DATA HISTORIS**:
  - Dilarang memodifikasi nilai transaksi pada nota, invoice, PO, atau penerimaan barang yang sudah berstatus selesai (*completed / paid*).

### 6.2 Keamanan & Isolasi Multi-Tenant (Strict Tenant Isolation)
- ❌ **DILARANG MELAKUKAN QUERY DATABASE TANPA SCOPING TENANT**:
  - Setiap query Eloquent atau Query Builder pada entitas tenant WAJIB menyertakan scope bisnis aktif:
    ```php
    // BENAR (Aman & Terisolasi Penuh)
    $business = \App\Support\Context::requireBusiness();
    $products = Product::where('business_id', $business->id)->get();

    // SALAH BESAR (Kebocoran Data Lintas Tenant / IDOR Risk!)
    $products = Product::all();
    ```
- ❌ **DILARANG MEMBYPASS MIDDLEWARE KEAMANAN**:
  - Dilarang melepas middleware inti: `auth:web`, `auth:admin`, `auth:customer`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `require.role:*`, dan `entitlement:*`.

### 6.3 Integritas Formulir & Proteksi Eksploitasi
- ❌ **DILARANG MENGHILANGKAN TOKEN CSRF & METHOD SPOOFING**:
  - Setiap tag `<form>` wajib menyertakan `@csrf`. Formulir `PUT`, `PATCH`, atau `DELETE` wajib mempertahankan `@method(...)`.
- ❌ **DILARANG MENGHAPUS VALIDASI REQUEST**:
  - Validasi backend (`required`, `numeric`, `min`, `max`, `exists`, `unique`) tidak boleh dihapus atau dilemahkan.
  - Dilarang memasukkan input mentah ke `DB::raw()` tanpa parameter binding.
  - Dilarang merender output HTML bebas yang belum di-escape (hindari `{!! $var !!}` kecuali HTML yang telah disanitasi).

### 6.4 Larangan Penghapusan Sepihak (Zero Silent Deletions)
- ❌ **DILARANG DIAM-DIAM MENGHAPUS FITUR, MENU, ATAU ROUTE**:
  - Penghapusan atau penggabungan rute lama WAJIB menyediakan *redirect* atau alias rute guna menjamin *backward-compatibility* dan mencegah *broken links* pada bookmark pengguna.

---

# 7. MATRIKS AUDIT KESENJANGAN (GAP ANALYSIS) 4-DIMENSI: ADMIN, OWNER, CUSTOMER, & OTOMASI

Setiap modul wajib diaudit batas keamanannya (*security boundary*) dan kesenjangan pengalamannya (*experience gap*) antar 4 kuadran:

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

### 7.1 Peta Kesenjangan & Mitigasi Keamanan:
1. **Superadmin vs Owner**:
   - *Risiko*: Superadmin memodifikasi data operasional/stok tenant secara tidak sengaja.
   - *Mitigasi*: Seluruh mutasi admin wajib tercatat di *audit log* (`admin_id` terekam), tanpa memotong validasi integritas finansial tenant.
2. **Owner vs Customer (IDOR Shield)**:
   - *Risiko*: Pembeli A mengintip nota pembeli B via manipulasi ID transaksi di URL (`/customer/orders/{id}`).
   - *Mitigasi*: Verifikasi ganda menggunakan identitas global customer terikat (`auth:customer`) dan nomor WhatsApp terverifikasi OTP. Dilarang query pesanan jika parameter identitas bernilai `null`!
3. **Owner vs POS Staff (Privilege & Fraud Prevention)**:
   - *Risiko*: Kasir melakukan *void* transaksi atau *refund* sepihak untuk penggelapan dana.
   - *Mitigasi*: Aksi sensitif (Void, Refund, Buka Laci Kas Manual) WAJIB diproteksi verifikasi `supervisor_pin` yang di-hash (Bcrypt) dan dibatasi pembatasan frekuensi (*throttle:5,1*).
4. **Otomasi vs Kegagalan Jaringan (Fail-Safe Automation)**:
   - *Risiko*: Server WhatsApp Gateway terputus sehingga struk tidak terkirim otomatis, memicu kepanikan kasir/pelanggan.
   - *Mitigasi*: Wajib memiliki mode fallback ramah Boomer. Jika gateway offline, sediakan tombol instan: `[ 📲 Kirim Manual via WhatsApp Web / Aplikasi HP ]` dengan teks nota yang telah terformat rapi.

---

# 8. MANDAT OTOMASI SISTEM PENUH (TOTAL SYSTEM AUTOMATION DIRECTIVE)

Sistem COOCA dirancang agar **bekerja secara mandiri untuk pengguna**, membebaskan mereka dari rutinitas input data berulang:
1. **Auto-Journaling Ganda (Debit = Kredit)**: Transaksi penjualan POS, order online, PO pembelian, kas masuk/keluar, dan pelunasan piutang wajib otomatis menghasilkan jurnal akuntansi seimbang tanpa mengharuskan pengguna memahami bagan akun (*Chart of Accounts*).
2. **Auto-Stock & Auto-BOM**: Penjualan makanan/minuman racikan atau paket barang langsung memotong stok bahan baku mentah secara otomatis berdasarkan resep (*Bill of Materials*).
3. **Auto-Invoice & WhatsApp Dispatch**: Invoice digital terbit seketika dan terkirim otomatis via WhatsApp berisi ringkasan nota dan tautan struk resmi tanpa kasir mengetik manual.
4. **Auto-Reminder Piutang & Hutang**: Pengingat berkala otomatis via WhatsApp dan notifikasi dashboard untuk invoice yang mendekati atau melewati jatuh tempo.
5. **Auto-Reconciliation & Status Engine**: Transisi status pesanan (*Menunggu Pembayaran ➔ Diproses ➔ Siap Diambil/Dikirim ➔ Selesai*) terotomasi penuh via webhook pembayaran atau aksi kasir 1-klik.

---

# 9. MASTER DIREKTIF UI/UX APPLE DESIGN (HUMAN INTERFACE GUIDELINES v2.0)

Seluruh antarmuka COOCA mengadopsi standar resmi:
**COOCA APPLE HUMAN INTERFACE GUIDELINES (HIG) DESIGN SYSTEM v2.0**  
*(Terinspirasi dari presisi dan ketenangan macOS Sonoma, iOS 18, dan visionOS)*

## 9.1 Tiga Pilar Utama Apple HIG
1. **Clarity (Kejelasan Mutlak):**
   - **3-Second Glanceability:** Maksud halaman, metrik utama, dan aksi prioritas harus dapat dipahami dalam waktu 3 detik pertama setelah layar terbuka.
   - **Teks Bersih & Kontras Tinggi:** Rasio kontras minimal 4.5:1 (WCAG 2.1 AA).
   - **Ikon Fungsional:** Menggunakan font icon resmi (**Lucide Icons**) yang intuitif dan berpasangan dengan label jelas. Dilarang ikon ambigu tanpa konteks pada aksi kritis.
2. **Deference (Kerendahan Hati Antarmuka):**
   - UI adalah pelayan konten. Data keuangan, produk, dan stok menjadi bintang utama tanpa terganggu ornamen mencolok.
   - Dilarang menggunakan gradien neon murahan, drop-shadow kotor pekat, atau border tebal gelap.
   - Kanvas netral Apple (`#F2F2F7` light / `#000000` dark) dan kartu putih bersih (`#FFFFFF` light / `#1C1C1E` dark).
3. **Depth (Kedalaman Ruang & Layering Halus):**
   - **Level 0 (Canvas Base):** `#F2F2F7` (Light) / `#000000` (Dark)
   - **Level 1 (Card Bento Surface):** `#FFFFFF` (Light) / `#1C1C1E` (Dark)
   - **Level 2 (Elevated Hover Tile):** `#F9F9FB` (Light) / `#2C2C2E` (Dark)
   - **Level 3 (Modal / Floating Sheet):** Frosted Glass Material
   - **Material Frosted Glass Translucent (Vibrancy):**
     ```html
     class="backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08]"
     ```
   - **Hairline Border Halus:** Border tipis 1px semi-transparan `border-black/[0.06] dark:border-white/[0.08]`.

## 9.2 Geometri Squircle & Continuous Corner Radius
* **Outer Bento Card:** `rounded-[20px]` atau `rounded-[24px]` (Desktop) / `rounded-[16px]` (Mobile)
* **Inner Tile / Sub-Widget:** `rounded-[14px]` atau `rounded-[16px]`
* **Tombol & Kolom Input:** `rounded-[12px]` atau `rounded-[14px]`
* **Status Badge / Pills:** `rounded-full`
* **Mobile Bottom Sheet:** `rounded-t-[28px]`
* *Dilarang keras memakai `rounded-none`, `rounded-sm`, atau sudut kaku 2px–4px.*

## 9.3 Mikro-Interaksi Taktil & Apple Press States
```html
class="transition-all duration-150 ease-out active:scale-[0.98] hover:opacity-95"
```
* **Touch Target Minimum:** Minimal **44x44px** (Wajib **48px hingga 52px** untuk tombol aksi utama di smartphone kasir).
* **Jarak Antar Tombol:** Minimal **12px–16px** (mencegah salah pencet).

## 9.4 Jiwa & Karakter Brand Cooca (Brand Soul & Persona)
Cooca **BUKAN** template AI generik. Cooca adalah **Sistem Operasi Bisnis UMKM Nusantara yang Berjiwa, Jujur, Tangguh, dan Presisi**:
1. **Tenang & Berwibawa (Calm Confidence):** Menghormati beban pikiran pemilik UMKM dan kasir yang sibuk: berikan ketenangan visual, bukan karnaval warna. White space dibiarkan lapang sebagai udara bernapas.
2. **Kejujuran & Presisi Fungsional (Rock-Solid Functional Honesty):** Setiap angka moneter dan stok disajikan dengan kepastian matematis murni menggunakan tipografi tebal dan `tabular-nums`.
3. **Wibawa Tanpa Gimmick (Apple Restraint - Seni Menahan Diri):** Jangan membungkus teks ke dalam kapsul jika hierarki tipografi murni sudah cukup menjelaskannya.
4. **Kehangatan Manusiawi (Human Touch):** Bahasa Indonesia santun, bersahaja, lugas. **DILARANG KERAS** slogan klise AI yang hampa (*AI-Powered Synergy, Next-Gen Modular Ecosystem, Ultimate Solution*).

---

# 10. MANDAT ANTI-AI-TEMPLATE & ANTI-PILL-ABUSE (PEMBERANTASAN INFLASI KAPSUL & STIKER)

## 10.1 Larangan Mutlak Eyebrow Pills (Kapsul di Atas Judul)
* ❌ **DILARANG KERAS:** Menaruh badge/pill `rounded-full` di atas judul utama (H1) maupun section (H2) seperti: `🟢 AI-Powered Management System • 100% Gratis`, `Ekosistem Modular Terpadu`, `• Live Cloud`.
* ✅ **Solusi Bernyawa:** Gunakan **Pure Typographic Overline/Kicker**: teks murni tanpa kapsul (`text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`), anggun dan berwibawa.

## 10.2 Larangan Metric Cluttering (Menempelkan Kapsul di Samping Angka Utama)
* ❌ **DILARANG:** Menempelkan pill kecil di samping angka besar (contoh: `Rp 0` ditempeli pill `📈 ARR Rp 0.0 Juta/thn`).
* ✅ **Solusi Bernyawa:** Biarkan angka utama berdiri gagah (`text-3xl font-bold tabular-nums`). Keterangan sekunder diletakkan di bawah angka sebagai footnote teks murni yang tenang (`text-[12px] text-black/50 dark:text-white/50`).

## 10.3 Larangan Fake Pulse Dots (Titik Berkedip Palsu)
* ❌ **DILARANG:** Menaruh titik berkedip (`animate-pulse`) pada teks biasa, nama section, atau rentang waktu (misal: `Live 6 Bulan Terakhir`).
* Efek pulsing dot HANYA diizinkan untuk status perangkat keras fisik yang benar-benar tersambung (koneksi printer kasir thermal Bluetooth, barcode scanner, timbangan digital, atau koneksi WebSocket kritis).

## 10.4 Batasan Penggunaan Pill / Badge (Hanya untuk Siklus Hidup Entitas)
Badge kapsul (`rounded-full`) **HANYA** boleh digunakan untuk **Status Siklus Hidup Entitas Bisnis yang Berubah (Dynamic Lifecycle State)**:
1. **Status Pembayaran:** `Menunggu Pembayaran` (amber), `Lunas` (green), `Dibatalkan` (gray), `Ditolak` (red).
2. **Status Stok & Bahan:** `Stok Aman` (green), `Menipis` (amber), `Habis` (red).
3. **Status Akun:** `Aktif` (green), `Ditangguhkan` (red), `Superadmin` (blue).
* **Maksimal 1 Badge per Entitas:** Dilarang menaruh lebih dari 1 badge dalam satu baris data atau satu kartu bento.

## 10.5 Mandat Eliminasi Total Elemen Fluff (Hapus Sampahnya, Jangan Cuma Copot Bajunya)
* ❌ **DILARANG:** Menghilangkan kapsul tetapi membiarkan teks hiasan mengambang di layar (*"Sama Aja Bohong"*).
* Teks hiasan seperti `Live 6 Bulan Terakhir`, `Organik`, `Terverifikasi`, `Platform-Wide`, `AI-Powered Management System`, `• Live Cloud`, `INSIGHT`, `Bebas Biaya Langganan` adalah **sampah visual**.
* **ATURAN MUTLAK:** Jika sebuah teks atau label tidak memiliki nilai operasional nyata atau sudah tersirat dari konteksnya, **HAPUS TOTAL ELEMEN DAN TEKS TERSEBUT DARI BLADE VIEW! DILARANG MENINGGALKAN TEKS POLOS!**

## 10.6 Mandat Larangan Mutlak Emoticon / Emoji pada UI (Strict No-Emoji Rule)
* ❌ **DILARANG KERAS:** Menggunakan emoticon atau emoji karakter Unicode (seperti 🚀, ✨, 💡, 👥, 🏆, 🎟️, 📦, ⚡, 🔥, 🟢, 📈, 💬, 🏢, dsb.) pada seluruh antarmuka pengguna (Buttons, H1/H2, Bento Cards, Tabs, Dialog Konfirmasi, Alert Banner, Status Badges, Tabel).
* ✅ **Solusi Wajib:** **HANYA GUNAKAN FONT ICON RESMI SISTEM (Lucide Icons)!** Gunakan `<i data-lucide="..." class="..."></i>` atau SVG inline presisi.

---

# 11. MANDAT ANTI-EXCESSIVE-TEXT & BAHASA LUGAS RAMAH PENGGUNA

## 11.1 Dilarang Memenuhi UI dengan Teks yang Tidak Perlu
AI Agent **DILARANG** menambahkan paragraf penjelasan panjang, deskripsi berulang yang sudah jelas dari judulnya, subtitle pada setiap kartu, atau jargon teknis (*SKU, BOM, COGS, Void, Tenant Context*).

## 11.2 Kamus Standar Label Tombol Aksi Utama (Maksimal 1 Kata Kerja Murni di Dalam Form)
Tombol adalah pemicu aksi (*action trigger*), bukan tempat mengulang konteks judul kartu/form:
* ❌ *Salah:* **"Simpan Pengaturan Google & Sistem"** → ✅ *Cukup:* **"Simpan"**
* ❌ *Salah:* **"Simpan Pengaturan SMTP"** → ✅ *Cukup:* **"Simpan"**
* ❌ *Salah:* **"Simpan Data Produk Baru"** → ✅ *Cukup:* **"Simpan"**
* ❌ *Salah:* **"Lakukan Proses Penghapusan Akun"** → ✅ *Cukup:* **"Hapus"**
* ❌ *Salah:* **"Lihat Rincian Selengkapnya Transaksi"** → ✅ *Cukup:* **"Lihat"**
* ❌ *Salah:* **"Ubah Rincian Profil Administrator"** → ✅ *Cukup:* **"Edit"** atau **"Ubah"**
* ❌ *Salah:* **"Kirim Email Uji Coba"** → ✅ *Cukup:* **"Kirim"**
* ❌ *Salah:* **"Batalkan Operasi Ini"** → ✅ *Cukup:* **"Batal"**

**Kamus Standar:**
* **`Simpan`** : Formulir pembuatan, pembaruan, dan pengaturan.
* **`Hapus`** : Konfirmasi atau pemicu penghapusan data.
* **`Edit` / `Ubah`** : Membuka form penyuntingan data.
* **`Lihat`** : Membuka rincian data / detail transaksi.
* **`Batal`** : Menutup modal atau membatalkan dialog.
* **`Kirim`** : Pengiriman broadcast atau pesan uji coba.
* **`Salin`** : Menyalin teks / API key ke clipboard.
*(Pengecualian 2 kata hanya untuk CTA index di luar form/tabel: "Tambah Produk", "Ekspor Excel", "Cetak Struk").*

---

# 12. SISTEM SPACING ADAPTIF & RUANG BERNAPAS (GENEROUS 8PT GRID)

UI COOCA **DILARANG PADAT, SESAK, ATAU BERDEMPETAN**.

| Area Tata Letak | Standar Mobile (<640px) | Standar Tablet (640–1023px) | Standar Desktop (1024px+) |
|---|---|---|---|
| **Jarak Antar Elemen Kecil** | 6px – 8px | 8px | 8px |
| **Jarak Antar Kontrol / Tombol** | 10px – 12px | 12px – 16px | 12px – 16px |
| **Jarak Antar Kartu (Grid Gap)** | 12px (`gap-3`) | 16px (`gap-4`) | 16px – 20px (`gap-4 sm:gap-5`) |
| **Padding Dalam Kartu** | **14px – 16px (`p-3.5`–`p-4`)** | **20px (`p-5`)** | **24px (`p-6`)** |
| **Jarak Antar Section** | 16px – 20px | 24px | 24px – 32px |
| **Margin Horizontal Halaman** | `px-3` | `px-6` | `px-8 max-w-[1440px] mx-auto` |
| **Padding Bawah Halaman (Safe Area)** | **`pb-28` s/d `pb-32` (MUTLAK)** | `pb-16` | `pb-10` |

*Perhatian Khusus Mobile:* Dilarang memberikan `p-6` atau `p-8` pada kartu mobile karena akan memakan 48px–64px lebar layar smartphone yang hanya 360px–390px!

---

# 13. MATRIKS TIPOGRAFI LINTAS PERANGKAT & STANDAR SF PRO

Font stack resmi sistem:
```css
font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
```

## 13.1 Skala Font Responsif Komprehensif (Apple Dynamic Type Scale)

| Peran Tipografi | Mobile (<640px) | Tablet (640–1023px) | Desktop (1024px+) | Weight | Line Height | Keterangan Khusus |
|---|---|---|---|---|---|---|
| **Large Title** | `text-[22px]–text-2xl (24px)` | `text-3xl (28px)` | `text-3xl–text-4xl (32–34px)` | 700 (Bold) | `leading-tight (1.2)` | Judul utama halaman (Dashboard, Katalog). |
| **Title 1 / Section** | `text-xl (20px)` | `text-2xl (24px)` | `text-2xl (24px)` | 600 (Semibold) | `leading-snug (1.25)` | Judul kelompok kartu bento. |
| **Title 2 / Card** | `text-[17px]–text-lg (18px)` | `text-lg (18px)` | `text-xl (20px)` | 600 (Semibold) | `leading-snug (1.3)` | Judul widget/kartu bento. |
| **Headline** | `text-[16px]` | `text-[16px]` | `text-[16px]` | 600 (Semibold) | `leading-normal (1.35)` | Baris nama produk/data penting. |
| **Body (Teks Utama)** | `text-[15px]–text-[16px]` | `text-[15px]–text-[16px]` | `text-[14px]–text-[15px]` | 400 (Regular) | `leading-relaxed (1.5)` | Teks deskripsi, paragraf bacaan nyaman. |
| **Form Input / Select** | **`text-[16px]` (MUTLAK)** | `text-[14px]–text-[15px]` | `text-[14px]` | 400 (Regular) | `leading-normal (1.4)` | **Wajib 16px di mobile (anti-auto-zoom iOS).** |
| **Subheadline** | `text-sm (14px)` | `text-sm (14px)` | `text-[13px]–text-sm (14px)` | 500 (Medium) | `leading-normal (1.4)` | Teks sekunder pendamping headline. |
| **Footnote / Helper** | `text-[13px]` | `text-[13px]` | `text-[12px]–text-[13px]` | 400 (Regular) | `leading-normal (1.35)` | Petunjuk form, label bantuan. |
| **Caption / Badge** | `text-xs (12px)` | `text-xs (12px)` | `text-[11px]–text-xs (12px)` | 600 (Semibold) | `leading-none (1.2)` | Status pills, badge filter. |
| **Angka / Moneter** | **`tabular-nums`** | **`tabular-nums`** | **`tabular-nums`** | 600–700 | `leading-none` | **Rupiah, stok, persentase, tanggal.** |

## 13.2 Aturan Mutlak Anti-Auto-Zoom Input Mobile
Wajib menerapkan kelas responsif pada seluruh `<input>`, `<select>`, dan `<textarea>`:
```html
class="text-[16px] sm:text-[14px] ..."
```

## 13.3 Tabular Figures (`tabular-nums`)
Seluruh nilai Rupiah, jumlah stok, nomor nota, tanggal/jam, dan persentase **WAJIB** menyertakan class `tabular-nums` agar sejajar vertikal secara matematis.

## 13.4 Kontras Ketebalan & Pembatasan Bobot
* Hanya gunakan 4 bobot: `400 (Regular)`, `500 (Medium)`, `600 (Semibold)`, `700 (Bold)`.
* **DILARANG KERAS:** Memakai `font-black` atau bobot `900`.

---

# 14. ARSITEKTUR LAYOUT & KOMPONEN APPLE HIG MENYELURUH

## 14.1 Sidebar Menu (macOS Sonoma Source List Strict Standard)
* **Lebar Baku Desktop 288px (`w-72`) & Offset Kanvas (`lg:pl-72`)**: Lebar sidebar desktop WAJIB `w-72` (288px / 18rem), **DILARANG sempit `w-64`** yang memicu teks tertekuk. Kanvas utama desktop wajib menerapkan `lg:pl-72`.
* **Mandat Satu Baris Mutlak (*Strict Single-Line Directive*)**: Seluruh item navigasi **DILARANG KERAS MEMBUNGKUS / BERTUMPUK MENJADI 2–3 BARIS**. Seluruh label teks wajib menerapkan kelas:
  ```html
  <span class="whitespace-nowrap truncate min-w-0 flex-1">...</span>
  ```
  Gunakan penamaan ringkas Apple (*Langganan & Billing*, *Paket & Harga*, *Rekening Bank*, *Token AI*, *WhatsApp Gateway*, *Google OAuth*, *Server SMTP*).
* **Scrollbar Ramping Anti-Windows (*Sleek Custom Scrollbar Directive*)**:
  - Kontainer sidebar WAJIB menerapkan kelas `.sidebar-scroll` dengan styling ramping 4px:
    ```css
    .sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.15) transparent;
    }
    .dark .sidebar-scroll {
        scrollbar-color: rgba(255, 255, 255, 0.18) transparent;
    }
    .sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.15);
        border-radius: 9999px;
    }
    .dark .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.18);
    }
    ```
  - **DILARANG KERAS** membiarkan scrollbar tebal default Windows (17px) muncul.
* **Latar & Material Translucent**: `backdrop-blur-2xl bg-[#F2F2F7]/80 dark:bg-[#1C1C1E]/80 border-r border-black/[0.06] dark:border-white/[0.08]`.
* **Item Navigasi Squircle & State Aktif Apple**:
  - Tinggi item 40px, sudut `rounded-[12px] px-3 font-medium text-[13.5px]`.
  - State Aktif: `bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold`.
  - State Inaktif: `text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]`.
* **Footer Profil Pengguna Inset**: Bento card mini `rounded-[16px] p-2.5` dengan pembungkus teks `min-w-0 flex-1 truncate`.
* **Mandat Kontensi Sidebar**: Wajib `overflow-y-auto overflow-x-hidden`. Dilarang scrollbar horizontal muncul di sidebar.

## 14.2 Topbar / Header (macOS Sonoma Toolbar & Zero Top-Edge Clipping Directive)
* **Ketinggian Aman & Anti-Clipping**: Ketinggian header wajib proporsional (`h-[68px] sm:h-[72px]` atau `min-h-[64px] py-2 sm:py-2.5 px-4 sm:px-8 sticky top-0 z-30 backdrop-blur-xl bg-[#F2F2F7]/75 dark:bg-[#000000]/75 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4`).
* **DILARANG KERAS TEKS TERPOTONG DI BATAS ATAS VIEWPORT (*Zero Top-Edge Clipping*)**:
  - Baris teks teratas (`eyebrow` sistem 11px) WAJIB memiliki jarak aman dari batas atas (`leading-none mb-1` dan padding vertikal terlindungi):
    ```html
    <div class="min-w-0 flex flex-col justify-center">
        <span class="text-[10.5px] sm:text-[11px] font-semibold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] leading-none mb-1">...</span>
        <h1 class="text-xl sm:text-2xl font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight leading-snug truncate">...</h1>
    </div>
    ```
* **Pill Control / Segmented Switcher**: Wadah pill terpadu `p-1 rounded-full bg-black/[0.05] dark:bg-white/[0.08]`.
* **Primary Action CTA**: Menggunakan System Blue Apple `bg-[#007AFF] text-white rounded-[14px] px-4 py-2 font-semibold shadow-sm hover:brightness-105 active:scale-[0.98]`.

## 14.3 Content Body Canvas (Bento Grid Architecture)
* **Kontainer Max-Width**: `max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8`.
* **Horizontal Table Containment**: Seluruh tabel dibungkus dalam kartu bento dengan kontainer geser horizontal (`overflow-x-auto scrollbar-thin`) dengan border hairline tipis.

## 14.4 Dual-Footer Architecture
* **Mobile / Tablet Mode (`md:hidden`)**: Full-Style Floating Bottom Navigation Bar (iOS 18) mengambang `fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 pb-[env(safe-area-inset-bottom)] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 rounded-[24px]` dengan elevated center action trigger.
* **Desktop Mode**: Clean Minimalist Hairline Footer `border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px] text-black/50 dark:text-white/50`.

## 14.5 Mandat Anti-Kerusakan Dimensi SVG & Ikon (Strict SVG Dimensioning)
* Setiap elemen `<svg>` WAJIB menyertakan atribut eksplisit `width="..." height="..."` DAN kelas utilitas CSS presisi (contoh: `width="18" height="18" class="w-[18px] h-[18px] shrink-0"` atau `w-4 h-4` / `w-5 h-5`).
* Dilarang kelas pecahan non-standar (`w-4.5` / `h-4.5`) tanpa registrasi di konfigurasi Tailwind.
* Hal ini mencegah SVG meledak menjadi raksasa yang menutupi menu atau merusak layout.

## 14.6 Harmoni Warna Semantik & Dual-Mode
* **Palet Sistem**: System Blue (`#007AFF` / `#0A84FF`), System Green (`#34C759` / `#30D158`), System Orange (`#FF9500` / `#FF9F0A`), System Red (`#FF3B30` / `#FF453A`), System Indigo (`#5856D6` / `#5E5CE6`), System Purple (`#AF52DE` / `#BF5AF2`).
* **Light Mode**: Background `#F2F2F7`, Card `#FFFFFF`, Teks primer `#000000`, sekunder `text-black/60`.
* **Dark Mode**: Background `#000000` / `#1C1C1E`, Card `#1C1C1E` / `#2C2C2E`, Teks primer `#FFFFFF`, sekunder `dark:text-white/60`.
* **Dilarang Warna Solid Jenuh**: Dilarang background kartu warna jenuh pekat. Gunakan tinted badge pill (`bg-{color}/12 text-{color}`).

---

# 15. FILOSOFI ANTARMUKA TANPA PANDUAN (ZERO-MANUAL / SELF-EXPLANATORY UI)

Dirancang khusus agar dapat dioperasikan secara percaya diri oleh generasi **Boomer (50–65+ tahun) dan Milenial Akhir (40+ tahun)** yang sering cemas saat melihat istilah teknis:
1. **Prinsip Obvious Affordance (Tombol Berkata Kerja Nyata)**:
   - ✅ `[ + Tambah Barang Baru ]` (bukan hanya ikon `+`)
   - ✅ `[ Simpan & Cetak Struk ]` (bukan `Submit` / `Save`)
   - ✅ `[ Kirim Nota ke WhatsApp Pelanggan ]`
   - Dilarang membuat tombol aksi kritis hanya berupa ikon kecil tanpa teks (*mystery meat navigation*).
2. **Kaidah 3 Kolom Pokok (Anti-Intimidasi Form)**:
   - Form utama (misal Tambah Produk) secara default hanya menampilkan 3 kolom esensial:
     1. **Nama Barang / Jasa**
     2. **Kategori**
     3. **Harga Jual (Rp)**
   - Opsi lanjutan (Barcode, Resep Bahan/BOM, Modal Pokok, Min Stok) disembunyikan rapi di dalam akordeon:  
     `[ Atur Modal Beli, Stok Gudang & Resep (Opsional) ▾ ]`.
3. **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*)**:
   - Di setiap dialog konfirmasi (Hapus/Batal/Void), wajib menyertakan kalimat penenang menggunakan ikon Lucide Info (DILARANG EMOJI):  
     *“Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.”*
4. **Format Ribuan Otomatis**:
   - Input nominal uang wajib otomatis memformat pemisah ribuan titik (`Rp 100.000`) secara real-time untuk mencegah salah ketik nol berlebih.
5. **Glosarium Bahasa Indonesia Lugas**:
   - *HPP / COGS* ➔ Modal Pokok / Biaya Bahan
   - *Stock Reversal* ➔ Pengembalian Bahan
   - *Void Transaction* ➔ Pembatalan Transaksi
   - *Tenant Context* ➔ Pemisahan Toko

---

# 16. MANDAT KONSOLIDASI & PENGGABUNGAN UI (UI UNIFICATION DIRECTIVE)

> **Aturan Emas:** *"Jika dua atau tiga antarmuka saling melengkapi dan mengelola entitas yang sama, MAKA WAJIB DIGABUNG menjadi satu halaman terpadu berbasis Tab atau Master-Detail."*

| Halaman Terpisah (Pola Lama) | Rekomendasi Penggabungan (Pola Baru Bersatu) | Manfaat Bagi Pengguna Usia 40+ |
| :--- | :--- | :--- |
| • `Pelanggan` (`/customers`)<br>• `CRM & Member` (`/crm/members`) | ➔ **Pusat Pelanggan & Loyalitas** (`/customers`) dengan Tab:<br>`[ Semua Pelanggan ] [ Member & Poin ] [ Voucher Diskon ]` | Satu tempat terpadu untuk melihat piutang, kontak WhatsApp, dan poin hadiah. |
| • `Katalog Produk` (`/products`)<br>• `Jasa & Layanan` (`/services`) | ➔ **Katalog Usaha** (`/products`) dengan Segmented Control:<br>`[ Semua ] [ Barang Fisik (Ada Stok) ] [ Jasa / Servis (Bebas Stok) ]` | Tidak bingung membedakan menu jasa dan barang; form input menyesuaikan otomatis. |
| • `Kas & Rekening Bank` (`/finance/cash-bank`)<br>• `Buku Kas & Ledger` (`/finance/cash-bank/ledger`) | ➔ **Pusat Kas & Bank** (`/finance/cash-bank`):<br>Atas: Saldo & Tombol Cepat (Kas Masuk / Keluar).<br>Bawah: Tabel Mutasi Transaksi Terpadu. | Owner langsung melihat uang tunai di laci, saldo rekening, dan mutasi keluar-masuk di satu layar. |
| • `Master Data Supplier` (`/suppliers`) | ➔ Dipindahkan ke dalam grup navigasi **Pembelian & Vendor** (berdampingan dengan PO, Tagihan, dan Retur). | Tidak perlu mencari menu Supplier di bagian paling bawah dashboard. |

---

# 17. MANDAT MASTER-DETAIL POP-UP / MODAL SHEET FIRST & INLINE QUICK-ADD

## 17.1 Modal-First Architecture pada Halaman Index & Standar Full Layout XXL
* Operasi **Show (Detail)**, **Create (Tambah Baru)**, dan **Edit (Ubah)** pada seluruh halaman index **WAJIB DISEDIAKAN DALAM BENTUK POP-UP / MODAL SHEET LANGSUNG DI HALAMAN INDEX TANPA REDIRECT (*Zero Navigation Jumps*)**.
* Filter pencarian, filter kategori, sorting, dan posisi pagination tetap utuh saat modal ditutup.
* **Standar Ukuran: Wajib Full Layout XXL untuk Seluruh Operasi Utama**:
  - DILARANG menggunakan modal sempit (`max-w-md` atau `max-w-lg`) untuk form ERP, transaksi, dan master-detail karena membuat form berjejal, memicu scroll vertikal berlebihan, dan memotong tabel rincian transaksi.
  - Seluruh modal operasional (Tambah/Ubah Produk, Pembelian, Penjualan POS, Customer CRM, Kas & Bank, Approval Langganan, Jurnal, dan Laporan) **WAJIB menggunakan Full Layout XXL (`max-w-5xl` hingga `max-w-7xl` / `max-w-[95vw]`)** yang lapang, elegan, dan memanfaatkan ruang layar monitor desktop secara optimal.

### 17.1.1 Matriks Responsivitas Modal Pop-Up Lintas Perangkat (Desktop, Tablet, Mobile)

| Parameter Desain | Layar Desktop (>= 1024px) | Layar Tablet Kasir & iPad (640px – 1023px) | Layar Smartphone Mobile (< 640px) |
|---|---|---|---|
| **Tipe Kontainer** | **Full Layout XXL Centered Bento Dialog** | **Centered Responsive Bento Modal** | **Apple Full-Responsive Bottom Sheet** meluncur dari bawah layar |
| **Dimensi Lebar** | `w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl mx-auto` | `w-full max-w-[92vw] md:max-w-3xl lg:max-w-4xl mx-auto` | `w-full max-w-full inset-x-0 bottom-0` |
| **Ketinggian (Height)** | `max-h-[90vh] sm:max-h-[92vh] flex flex-col my-auto` | `max-h-[90vh] flex flex-col my-auto` | `max-h-[94vh] flex flex-col` |
| **Radius Sudut** | `rounded-[24px]` squircle kontinu Apple | `rounded-[22px]` squircle kontinu Apple | `rounded-t-[28px]` membulat di sudut atas |
| **Pegangan (Grab Bar)** | Tidak ada | Tidak ada | Wajib (`w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5`) |
| **Struktur Grid Body** | **Multi-Kolom Bento 2 s/d 3 Kolom** (`grid grid-cols-1 lg:grid-cols-12 gap-6`) | **2 Kolom Seimbang** (`grid grid-cols-1 md:grid-cols-2 gap-4`) | **1 Kolom Vertikal Murni** (`grid-cols-1 gap-3.5`) |
| **Header Modal** | Sticky frosted glass, Title 20px, subheadline, close button | Sticky frosted glass, Title 18px, close button | Sticky header ringkas, Title 17px, close button (target 44px) |
| **Footer Aksi** | Sticky bottom frosted glass, tombol rata kanan `justify-end gap-3.5` | Sticky bottom frosted glass, tombol rata kanan `justify-end gap-3` | Sticky bottom action bar menempel jempol, tombol full-width, padding safe area |
| **Font Input Form** | `text-[14px]` | `text-[14px]` – `text-[15px]` | **Wajib minimal 16px (`text-[16px] sm:text-[14px]`) (anti auto-zoom iOS)** |
| **Touch Target Tombol** | `h-10` s/d `h-11` (40px–44px) | `h-11` s/d `h-12` (44px–48px) | `h-12` (48px–52px) nyaman jempol |

### 17.1.2 Anatomi 3-Bagian Baku Modal XXL Bento Apple HIG
1. **Header Modal (Sticky Top)**:
   - Kontainer: `sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4`.
   - Sisi Kiri: Ikon squircle berlatar lembut (`w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF]`) + Judul Title 2 tebal + Subtitle teks murni (*Zero Eyebrow Pill / Zero Emoji*).
   - Sisi Kanan: Tombol tutup Apple Circle Close Button (`w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all`).
2. **Body Konten XXL (Scrollable Bento Container)**:
   - Kontainer: `flex-1 overflow-y-auto p-5 sm:p-8 space-y-6 overscroll-contain sidebar-scroll`.
   - Di Desktop: Mampu memuat grid 12 kolom bento (misal: 8 kolom rincian barang/transaksi + 4 kolom kartu kalkulasi moneter, status, dan data relasi) tanpa sesak.
   - Di Tablet: Mampu memuat 2 kolom modular berdampingan yang seimbang dan mudah diisi saat tablet kasir diposisikan landscape/portrait.
   - Di Mobile: Alur 1 kolom vertikal yang lapang dengan ruang sentuh lega.
3. **Footer Aksi (Sticky Bottom Action Bar)**:
   - Kontainer: `sticky bottom-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-3.5 sm:py-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3`.
   - Sisi Kiri: Pesan penenang jiwa (*No-Panic Feedback*) dengan ikon Lucide `info`: *"Tenang, riwayat transaksi Anda aman tersimpan."*
   - Sisi Kanan: Tombol pemicu aksi satu kata kerja murni (**"Batal"** sekunder + **"Simpan"** primary Apple System Blue `h-11 sm:h-12 px-6 rounded-[12px] font-semibold text-white bg-[#007AFF] shadow-sm hover:brightness-105 active:scale-[0.98]`).

### 17.1.3 Template Kode Baku Blade Modal XXL Responsif
```html
<!-- Modal Backdrop & Container Wrapper -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <!-- Backdrop Overlay -->
    <div x-show="showModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showModal = false"
        class="fixed inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm transition-opacity"></div>

    <!-- Alignment Wrapper: Bottom Sheet di Mobile, Centered di Tablet & Desktop -->
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6">
        <!-- Modal Card XXL -->
        <div x-show="showModal"
            x-transition:enter="ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
            x-transition:leave="ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0"
            @click.outside="showModal = false"
            class="w-full max-w-full sm:max-w-[92vw] md:max-w-3xl lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl max-h-[94vh] sm:max-h-[90vh] flex flex-col rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden">
            
            <!-- Mobile Grabber Bar -->
            <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

            <!-- Sticky Modal Header -->
            <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg sm:text-xl font-bold text-black dark:text-white truncate">Judul Modal XXL</h3>
                        <p class="text-xs sm:text-sm text-black/50 dark:text-white/50 truncate">Subheadline penjelasan fungsional singkat</p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Scrollable Content Body (Multi-Kolom Bento) -->
            <div class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6 overscroll-contain">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Area Utama (8 Kolom di Desktop) -->
                    <div class="lg:col-span-8 space-y-5">
                        <!-- Komponen Form / Tabel Bento -->
                    </div>
                    <!-- Area Samping / Ringkasan (4 Kolom di Desktop) -->
                    <div class="lg:col-span-4 space-y-5">
                        <!-- Ringkasan Moneter / Status Bento -->
                    </div>
                </div>
            </div>

            <!-- Sticky Modal Footer -->
            <div class="sticky bottom-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-3.5 sm:py-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pb-[max(1rem,env(safe-area-inset-bottom))]">
                <div class="hidden sm:flex items-center gap-2 text-xs text-black/50 dark:text-white/50">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Data tersimpan dengan enkripsi aman di cloud.</span>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="button" @click="showModal = false" class="w-full sm:w-auto h-11 px-5 rounded-[12px] text-sm font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] active:scale-[0.98] transition-all">Batal</button>
                    <button type="button" class="w-full sm:w-auto h-11 px-6 rounded-[12px] text-sm font-semibold bg-[#007AFF] text-white shadow-sm hover:brightness-105 active:scale-[0.98] transition-all">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
```

## 17.2 Inline Quick-Add Trigger `[ + ]` pada Dropdown / Select Option
Setiap dropdown master yang membutuhkan data relasi (Kategori, Satuan, Supplier, Pelanggan, Rekening Kas) **WAJIB menyediakan tombol `[ + ]` tepat di samping kanan dropdown**:
```html
<div class="flex items-center gap-2">
    <div class="relative flex-1">
        <select class="w-full text-[16px] sm:text-[14px] rounded-[12px] ...">...</select>
    </div>
    <button type="button" class="w-11 h-11 flex-shrink-0 rounded-[12px] bg-blue-50 dark:bg-blue-900/30 text-[#007AFF] font-bold text-lg flex items-center justify-center active:scale-95 transition-all" title="Tambah Cepat">+</button>
</div>
```
* Membuka mini modal sheet tanpa meninggalkan form utama.
* Menyimpan via endpoint AJAX yang aman.
* Menginjeksi opsi baru ke `<select>` dan memilihnya otomatis (*auto-select*).
* Form utama tidak kehilangan data yang sedang diinput oleh pengguna.

---

# 18. CETAK BIRU RESPONSIVITAS BENTO UI & ADAPTABILITAS MULTI-DEVICE

## 18.1 Smartphone: 360px–639px (Strict Zero-Breakage Rules)
* **Zero Horizontal Overflow**: Dilarang menggunakan lebar statis `w-[...]` > 300px. Seluruh teks di dalam flex container wajib menyertakan `min-w-0` dan `truncate`.
* **Bento Grid Adaptif 2-Kolom (`grid grid-cols-2 gap-3`)**: DILARANG menumpuk seluruh metrik KPI menjadi 1 kolom yang monoton! Seluruh kartu KPI ditata berdampingan 2 kartu per baris (`col-span-1`) dengan padding `p-3.5 sm:p-4`.
* **Hero Bento Card (`col-span-2`)**: Kartu utama menyajikan status operasional paling krusial.
* **Transformasi Tabel ke Card List**: Tabel lebar pada mobile disembunyikan (`hidden md:block`) dan digantikan oleh deretan kartu ringkas (*Card List View*) yang nyaman dibaca vertikal (`block md:hidden`). Jika tabel wajib tampil, bungkus dalam kontainer `overflow-x-auto`.
* **Bottom Navigation Safe Area**: Seluruh halaman mobile **WAJIB** menyertakan padding bawah **`pb-28` s/d `pb-32` (`pb-28 lg:pb-10`)** agar konten tidak tertutup floating bottom navbar.

## 18.2 Tablet Kasir POS: 640px–1023px
* Tata letak 2 hingga 3 kolom modular (`md:grid-cols-2 lg:grid-cols-3`).
* Katalog dan keranjang kasir POS tampil seimbang berdampingan dalam mode landscape.
* Tombol aksi minimal 48px agar nyaman bagi jari kasir.

## 18.3 Laptop & Desktop Lebar: 1024px–1920px+
* Kanvas terkontrol rapi: `max-w-[1440px] mx-auto`.
* Layout bento 3–4 kolom modular yang lapang dan seimbang.
* **Mandat Preservasi Desktop 100%**: Perbaikan layout mobile **DILARANG KERAS** merusak tampilan desktop yang sudah baik!

---

# 19. AUDIT SISTEM BERJALAN (CURRENT SYSTEM AUDIT) & KETERTELUSURAN HULU-KE-HILIR

## 19.1 End-to-End Traceability (Ketertelusuran Hulu-ke-Hilir)
Setiap workflow operasional wajib ditelusuri secara utuh:
```text
User
→ UI (Blade View / Bento Component)
→ JavaScript / AJAX (Fetch / Error Handling)
→ Route (routes/web.php / admin.php / customer.php)
→ Middleware (Auth, Tenant, Role, Rate-Limiting)
→ Authentication (Session, Multi-Guard)
→ Authorization / Permission (Gates / Policies)
→ Controller
→ Request Validation (Form Request)
→ Service / Action Domain Logic
→ Model (Eloquent Relations & Scopes)
→ Database (Migrations, Transactions, Locks)
→ Event / Queue Job (Auto-journal, WA Dispatch)
→ Notification / Third-party API
→ Final UI Response (Flash Message / JSON)
```
*Jangan pernah menyatakan fitur selesai hanya karena tampilan Blade UI sudah selesai dibuat!*

## 19.2 Audit Duplikasi (Reuse First)
```text
Reuse Existing
    ↓
Refactor Existing
    ↓
Consolidate Existing
    ↓
Create New Only If Necessary
```

---

# 20. PROTOKOL PENGUJIAN WAJIB & STANDAR 100% BEBAS EROR

Sebelum menyatakan tugas rekayasa selesai, AI Agent **WAJIB menjalankan dan memverifikasi pengujian nyata**:

```bash
# 1. Uji Sintaks PHP (Seluruh file PHP yang dimodifikasi)
php -l <file.php>

# 2. Uji Registrasi Rute & Bebas Collision
php artisan route:list

# 3. Eksekusi Test Suite Relevan
php artisan test
```

### Checklist Verifikasi Kualitas Eksekusi:
- [ ] **Lolos 100% (Zero Errors / Failures)**: `php artisan test` menunjukkan status `PASS` (0 failures, 0 errors).
- [ ] **Verifikasi Responsivitas Mobile (360px–430px)**: Bebas mutlak dari scroll horizontal (*zero horizontal overflow*), teks tidak terpotong.
- [ ] **Verifikasi Input Mobile 16px**: Seluruh `<input>`, `<select>`, `<textarea>` menerapkan `text-[16px] sm:text-[14px]`.
- [ ] **Verifikasi Touch Target**: Tombol aksi minimal 44x44px hingga 52px dengan sela antar-tombol minimal 12px.
- [ ] **Verifikasi Safe Area**: Halaman memiliki padding bawah aman `pb-28 lg:pb-10`.
- [ ] **Verifikasi Tabular Figures**: Nilai moneter, stok, nomor faktur, dan tanggal menyertakan `tabular-nums`.
- [ ] **Verifikasi No-Emoji**: Antarmuka 100% bebas dari emoji Unicode dan murni menggunakan Lucide Icons.
- [ ] Tidak ada exception 500, broken link, atau JavaScript console error.

---

# 21. KESIAPAN SOURCE CODE PRODUKSI & PEMBERSIHAN DATA TESTING (PRODUCTION HARDENING)

> **Catatan Kritis:** Kesiapan produksi di sini difokuskan pada **level source code aplikasi** agar tidak lagi berstatus testing/mock/dev bypass, **BUKAN sekadar konfigurasi `.env`**.

### 21.1 De-mocking & Eliminasi Bypass Pengujian:
* Hapus seluruh logika tiruan (mocking), stub dummy, atau bypass sementara (contoh: bypass OTP statis `123456`, bypass hak akses sementara, atau mock response di service).
* Pastikan controller dan service menjalankan logika produksi asli secara tangguh (*hardened real services*).

### 21.2 Pembersihan Data Testing & File Sampah (*Test Data Purge*):
* Hapus seluruh dummy record, order fiktif, user tester sementara, dan token uji coba dari database operasional.
* Hapus berkas temporary/file attachment uji coba pada direktori storage (`storage/app/public/...` atau `storage/tmp/`).

### 21.3 Sanitasi Kode Program (*Zero Debug Leftovers*):
* Dilarang menyisakan pemanggilan fungsi debugging mentah di source code:
  ```text
  dd()
  dump()
  ray()
  var_dump()
  console.log()
  ```

### 21.4 Kompilasi Aset & Optimasi Cache:
* Jalankan kompilasi aset rilis produksi: `npm run build`.
* Jalankan kompilasi cache performa: `php artisan route:cache` dan `php artisan view:cache`.

---

# 22. DOKUMENTASI BERKELANJUTAN 3 LAPIS (CONTINUOUS 3-LAYER DOCUMENTATION)

Setiap pekerjaan rekayasa yang mengubah sistem wajib memperbarui pengetahuan sistem secara berkelanjutan (*documentation is part of development*):

## Layer 1: Rekam Jejak Historis (`docs/AiWorkHistory.md`)
Mencatat rekaman historis terstruktur:
* **Work ID & Tanggal**
* **Konteks Bisnis & Masalah yang Dipecahkan**
* **File & Tabel yang Disentuh**
* **Workflow yang Terdampak**
* **Keputusan Teknis Penting**
* **Bukti Hasil Testing Nyata**
* **Status Verifikasi**

## Layer 2: Pengetahuan Sistem Terkini (`docs/system/`)
Memperbarui representasi kondisi sistem saat ini (*Current State Knowledge*):
* `modules/` & `features/`
* `workflows/` & `business-rules/`
* `permissions/` & `architecture/`

## Layer 3: Panduan Induk Kurasi (`docs/SYSTEM_GUIDE.md`)
Panduan induk tingkat tertinggi bagi Business Owner, Developer, QA, dan AI Agent. Wajib diperbarui jika terjadi perubahan cara kerja modul, arsitektur, standar UI/UX, atau alur bisnis.

---

### 22.1 Mandat Mutlak Pembaruan Simultan: `AiWorkHistory.md` + `SYSTEM_GUIDE.md`
> **ATURAN MUTLAK REKAYASA:**  
> *"Pencatatan riwayat pada `docs/AiWorkHistory.md` TIDAK PERNAH BERDIRI SENDIRI. Selain mencatatkan riwayat pada `docs/AiWorkHistory.md`, AI Agent WAJIB SELALU memperbarui Master System Guide `docs/SYSTEM_GUIDE.md` (serta berkas terkait di `docs/system/`) pada setiap task rekayasa!"*

1. **Anti-Log Saja (No Isolated History Logging):** DILARANG KERAS hanya menambahkan Work ID di `docs/AiWorkHistory.md` sementara `docs/SYSTEM_GUIDE.md` dibiarkan usang atau tidak tersentuh.
2. **System Guide Sebagai Sumber Kebenaran Hidup (*Living Source of Truth*):** `docs/SYSTEM_GUIDE.md` dibaca oleh Business Owner dan Developer untuk memahami kapabilitas sistem saat ini. Setiap penambahan fitur, perubahan alur, perbaikan bug krusial, penyesuaian aturan bisnis, atau pembaruan standar UI/UX (seperti standar Modal Pop-Up XXL) wajib langsung tercermin di `docs/SYSTEM_GUIDE.md`.
3. **Pekerjaan Dianggap Belum Selesai (Incomplete):** Jika AI Agent hanya memperbarui `docs/AiWorkHistory.md` tanpa memperbarui `docs/SYSTEM_GUIDE.md`, maka pekerjaan diklasifikasikan sebagai `PARTIAL` / `NEEDS_REVIEW` dan TIDAK BISA dinyatakan `VERIFIED` atau `COMPLETED`.

> **Aturan Sinkronisasi:** Ketiga layer harus konsisten 100% dan tidak boleh memiliki kebenaran yang saling bertentangan!

---

# 23. CHECKLIST KEPATUHAN SEBELUM SELESAI (DEFINITION OF DONE)

Pekerjaan hanya dapat dinyatakan selesai jika seluruh butir checklist ini tercentang:

- [ ] **History & Dokumentasi Terbaca**: Riwayat pekerjaan sebelumnya dan panduan sistem telah dibaca dan dipahami.
- [ ] **Gap Keamanan 4 Peran Tuntas**: Celah antara Admin, Owner, Customer (anti-IDOR), dan Otomasi (fail-safe) telah terproteksi.
- [ ] **Otomasi Sistem Terpasang**: Proses repetitif (jurnal akuntansi, potong stok BOM, kirim nota WA, transisi status) telah berjalan otomatis.
- [ ] **Bento UI Multi-Device Luwes**: Tata letak modular bento grid adaptif di smartphone, tablet kasir, dan desktop.
- [ ] **Keseragaman Konsep UI Lintas Perangkat**: 100% mewarisi bahasa desain Bento Apple HIG yang sama persis (squircle, frosted glass, tipografi tabular, warna semantik).
- [ ] **Full-Style Floating Bottom Navbar**: Tersedia bottom navigation bar mengambang bergaya iOS 18 pada smartphone/tablet (`fixed bottom-3`) dengan elevated center quick-action.
- [ ] **Modal-First pada Index (Full Layout XXL & Responsif)**: Seluruh aksi Show, Create, dan Edit disajikan via pop-up modal sheet Full Layout XXL (`max-w-5xl` s/d `max-w-7xl`) langsung di halaman index tanpa redirect (*zero navigation jumps*), responsif sempurna di Desktop, Tablet, dan Mobile.
- [ ] **Inline Quick-Add `[ + ]` pada Dropdown**: Dropdown relasi master memiliki tombol `[ + ]` inline dengan pop-up AJAX auto-select.
- [ ] **Mandat Anti-AI-Template & Anti-Pill Terpenuhi**: Bebas eyebrow pills (gunakan Pure Typographic Overline), bebas metric cluttering, bebas fake pulse dots, pill status maksimal 1 per baris, dan seluruh teks fluff telah dihapus total.
- [ ] **Mandat No-Emoji Terpenuhi**: 100% bebas dari emoji Unicode dan murni menggunakan Lucide Icons.
- [ ] **Tombol Aksi Lugas & Ringkas**: Tombol form menggunakan 1 kata kerja murni (*Simpan, Hapus, Edit, Lihat, Batal, Kirim, Salin*).
- [ ] **Ergonomi Ramah Boomer**: Input mobile font minimal 16px, tombol aksi mobile minimal 48–52px, format ribuan otomatis aktif (`Rp 100.000`), microcopy penenang jiwa aktif.
- [ ] **Keamanan Multi-Tenant Terjaga**: Query Eloquent terikat pada `Context::requireBusiness()` atau `$business->id`.
- [ ] **Integritas Finansial 100% Utuh**: Rumus subtotal, pajak, diskon, HPP, margin laba, dan jurnal akuntansi tidak berubah.
- [ ] **Sidebar macOS Sonoma Baku**: Lebar `w-72` (288px), content offset `lg:pl-72`, teks single-line nowrap truncate, dan custom sleek scrollbar 4px aktif.
- [ ] **Topbar Zero Top-Edge Clipping**: Ketinggian header aman dan teks teratas tidak pernah terpotong batas viewport.
- [ ] **Pengujian Otomatis Lolos 100%**: `php artisan test` berstatus PASS (0 failure, 0 error), `route:list` valid, `php -l` bebas syntax error.
- [ ] **Source Code Siap Produksi**: Bebas mock/stub/bypass, bebas fungsi debug (`dd()`, `dump()`, `ray()`, `console.log()`), aset terkompilasi rilis produksi.
- [ ] **Data Testing Dibersihkan Tuntas**: Database operasional dan storage bersih dari record/file dummy testing.
- [ ] **Dokumentasi 3 Lapis Terbarui Simultan**: Selain mencatatkan riwayat di `AiWorkHistory.md`, Master System Guide `docs/SYSTEM_GUIDE.md` (dan berkas terkait di `docs/system/`) WAJIB diperbarui secara bersamaan dan konsisten 100%. Dilarang hanya memperbarui history tanpa menyelaraskan System Guide.

---

# 24. FORMAT LAPORAN RESPONS AKHIR (FINAL RESPONSE FORMAT)

Setiap laporan penyelesaian tugas wajib disajikan dengan struktur terstandarisasi:

```text
## 1. History yang Dibaca
- File:
- Work ID relevan:
- Keputusan sebelumnya:

## 2. Kondisi Sistem Saat Ini
- Modul:
- Workflow:
- Route:
- Permission:
- Risiko:

## 3. Temuan Audit
- Masalah:
- Duplikasi:
- UI/UX & Apple HIG:
- Security & Multi-Tenant:
- Automation:
- Documentation:

## 4. Rencana Perubahan
- File yang diubah:
- File yang tidak diubah:
- Dampak:
- Risiko:
- Status persetujuan pengguna:

## 5. Implementasi
- Perubahan yang dilakukan:
- Workflow terdampak:

## 6. Testing & Verifikasi
- Command:
- Hasil:
- Error:
- Status verifikasi:

## 7. Kesiapan Produksi & Dokumentasi
- De-mocking & Debug Cleaned:
- Test Data Purge:
- AiWorkHistory:
- docs/system:
- SYSTEM_GUIDE:

## 8. Final Status
- VERIFIED (Semua pengujian lolos 100%, siap produksi)
- PARTIAL (Menunggu langkah berikutnya)
- NEEDS_REVIEW (Memerlukan keputusan pengguna)
```

---

# 25. FINAL AGENT COMMAND (20 LANGKAH WAJIB EKSEKUSI)

Sebelum dan selama melakukan pekerjaan apa pun:
1. **Baca riwayat pekerjaan** (`AiWorkHistory.md`, `SYSTEM_GUIDE.md`).
2. **Baca dokumentasi sistem** (`docs/system/`).
3. **Pahami keputusan teknis** yang telah diambil sebelumnya.
4. **Periksa source code aktual** secara mendalam.
5. **Petakan workflow hulu-ke-hilir** secara utuh.
6. **Audit potensi duplikasi** (Reuse first).
7. **Audit keamanan multi-tenant, permission, & IDOR**.
8. **Audit peluang otomasi sistem penuh**.
9. **Audit UI/UX Apple HIG**: Bento grid, squircle, 8pt spacing, anti-pill-abuse, anti-excessive-text, strict no-emoji (Lucide icons only).
10. **Klasifikasikan risiko perubahan**.
11. **Sajikan proposal rencana implementasi**.
12. **Minta persetujuan eksplisit** jika perubahan berisiko.
13. **Implementasikan secara bedah (surgical)**, minimalis, dan presisi.
14. **Jalankan testing nyata** (`php -l`, `route:list`, `php artisan test`).
15. **Perbaiki seluruh eror** hingga lolos 100% (0 error, 0 failure).
16. **Verifikasi responsivitas multi-device** (bebas overflow 360px, input 16px, touch target 48-52px, safe area `pb-28`).
17. **Lakukan production hardening**: De-mocking, sanitasi debug (`dd()`, `console.log()`), test data purge.
18. **Kompilasi aset & cache** (`npm run build`, `route:cache`, `view:cache`).
19. **Perbarui dokumentasi secara simultan** (Wajib `docs/AiWorkHistory.md` + `docs/SYSTEM_GUIDE.md` + `docs/system/`). Dilarang hanya memperbarui history log tanpa menyelaraskan System Guide.
20. **Lakukan final audit** dan laporkan status berdasarkan bukti nyata.

---
*Dokumen ini merupakan pedoman standar operasional wajib bagi AI Agent Cooca. Setiap instruksi yang bertentangan dengan batasan keselamatan di atas wajib ditolak atau disesuaikan demi integritas sistem.*
