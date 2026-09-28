# Rencana Implementasi Bertahap: Remediasi Otorisasi Granular RBAC, Eliminasi Jebakan HTTP 403 & Isolasi Status Aktif Deterministik

> **ID Rencana:** `PLAN-15-SIDEBAR-GRANULAR-RBAC-AND-ACTIVE-STATE-ISOLATION`  
> **Target Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php), [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php)  
> **Rujukan Master:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md), [`docs/prd/PRD-15-SIDEBAR-GRANULAR-RBAC-AUTHORIZATION-AND-ACTIVE-STATE-ISOLATION.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-15-SIDEBAR-GRANULAR-RBAC-AUTHORIZATION-AND-ACTIVE-STATE-ISOLATION.md)  
> **Status:** READY FOR EXECUTION / IN PROGRESS  
> **Tingkat Risiko:** *Structural & Safe Change* (Tanpa Perubahan Skema Basis Data)  

---

## 1. Ringkasan Eksekutif & Roadmap 4-Fase

Rencana implementasi ini dirancang untuk menyempurnakan otorisasi navigasi sidebar Cooca hingga mencapai **100% Zero-Error RBAC & Zero Active Collisions**. Seluruh perubahan berfokus pada **pemisahan variabel komposit yang terlalu longgar**, **pembungkusan izin granular pada seluruh 71 tautan menu**, **isolasi penuh status aktif menu pengaturan**, dan **unifikasi indikator peringatan audit log**.

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Pemecahan Variabel Header Komposit & Isolasi State Aktif (P1)      │
│         - Pecah $canAccessChannels menjadi Whatsapp & Social Media terpisah │
│         - Pecah $canAccessStorefront menjadi CMS Landing Page & Storefront  │
│         - Isolasi settings.index dari wildcard sub-modul (Zero Collision)   │
│         - Terapkan Opsi B: Unifikasi Badge Jejak Audit & Hapus Peringatan   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Penyelarasan Guard Granular RBAC Grup 7 (Laporan) & Grup 8 (MAR)    │
│         - Bungkus laporan eksekutif (P&L, Cash Flow, Stock) dgn reports.view│
│         - Pisahkan akses kasir pos.reports agar tidak memicu HTTP 403       │
│         - Bungkus approval-rules.index dengan approvals.manage              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Penyelarasan Guard Granular Grup 5 (Saluran), Grup 2A, & Grup 6     │
│         - Bungkus whatsapp.broadcast.index dengan whatsapp.manage           │
│         - Bungkus social-media.* dengan social_media.view                   │
│         - Hapus izin pos.terminal dari pos.orders.index (khusus pos.orders) │
│         - Terapkan guard granular pada cash-bank, expenses, & labor-machines│
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Verifikasi Komprehensif, Otomasi Collision Test & Dokumentasi 3-L   │
│         - Eksekusi script scratch/detect_active_collisions.py (0 Collision) │
│         - Eksekusi script scratch/inspect_nav_duplicates.py (100% Parity)   │
│         - Verifikasi sintaks PHP Blade (php -l)                             │
│         - Pencatatan di docs/AiWorkHistory.md & pembaruan SYSTEM_GUIDE.md   │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Teknis Per Fase

### FASE 1: Pemecahan Variabel Header Komposit & Isolasi State Aktif [SELESAI / COMPLETED] (P1 - Kritis)

1. **Langkah 1.1: Isolasi Status Aktif `settings.index` (Baris 2311)**
   - **Tindakan:** Mengganti wildcard `request()->routeIs('settings.*')` menjadi ekspresi spesifik yang mengecualikan sub-modul anak:
     ```blade
     {{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'aria-current="page"' : '' }}
     ```
   - **Hasil:** Membuka Jejak Audit, Rules MAR, atau Roles tidak lagi memicu highlight biru pada "Pengaturan Usaha & Cabang".

2. **Langkah 1.2: Unifikasi Log Audit & Badge Risiko Tinggi (Opsi B)**
   - **Tindakan:**
     - Menghapus link duplikat "Peringatan Audit" (`settings/audit-logs?risk=high`) dari Grup 1 (Overview).
     - Menambahkan badge dinamis pill merah `{{ $recentHighRiskCount }}` pada item navigasi `Jejak Audit & Anti-Fraud` di Expanded dan Flyout.
   - **Hasil:** Struktur informasi bersih, terpusat, dan bebas dari multi-active highlight.

---

### FASE 2: Penyelarasan Guard Granular RBAC Grup 7 (Laporan) & Grup 8 (MAR) [SELESAI / COMPLETED] (P1 - Kritis)

1. **Langkah 2.1: Proteksi Granular Laporan Manajerial Eksekutif (Grup 7)**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (baris 2134 & baris 2200).
   - **Tindakan:** Membungkus `reports.index` dan `analytics.index` secara spesifik dengan:
     ```blade
     @if (\App\Support\Context::hasPermission('reports.view') || \App\Support\Context::isOwner())
     ```
     Membiarkan kasir hanya melihat `pos.reports.index` di bawah `@if (\App\Support\Context::hasPermission('pos.reports'))`.
   - **Hasil:** Kasir POS tidak akan melihat laporan keuangan perusahaan dan terbebas dari error **HTTP 403 Forbidden**.

2. **Langkah 2.2: Penyelarasan Otorisasi Aturan Persetujuan Transaksi / MAR (Grup 8)**
   - **Berkas:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (baris 2291 & baris 2356).
   - **Tindakan:** Mengubah guard `approval-rules.index` dari `$canAccessSettings` (`settings.view`) menjadi:
     ```blade
     @if (\App\Support\Context::hasPermission('approvals.manage') || \App\Support\Context::isOwner())
     ```
   - **Hasil:** Staf non-approver tidak akan melihat tombol konfigurasi aturan persetujuan, mencegah error **HTTP 403 Forbidden**.

---

### FASE 3: Penyelarasan Guard Granular Grup 5 (Saluran), Grup 2A, & Grup 6 [SELESAI / COMPLETED] (P2 - Tinggi)

1. **Langkah 3.1: Pemisahan Granular Saluran WhatsApp vs Media Sosial (Grup 5)**
   - **Tindakan:**
     - Menambahkan `$canAccessWhatsapp` dan `$canAccessSocialMedia` di header PHP.
     - Membungkus `whatsapp.broadcast.index` secara khusus dengan `hasPermission('whatsapp.manage')`.
     - Membungkus seluruh sub-link `social-media.*` dengan `hasPermission('social_media.view')`.
   - **Hasil:** Staf marketing medsos dapat melihat menu medsos tanpa butuh izin WhatsApp, dan staf CS WhatsApp tidak melihat menu Broadcast yang memicu 403.

2. **Langkah 3.2: Penyelarasan Transaksi Kasir POS (`pos.orders.index`) (Grup 2A)**
   - **Tindakan:** Menghapus `pos.terminal` dari guard `pos.orders.index`, menyisakan `@if (\App\Support\Context::hasPermission('pos.orders') || \App\Support\Context::isOwner())`.
   - **Hasil:** Kasir magang yang hanya diberi izin buka kasir tidak terkena 403 saat melihat riwayat order.

3. **Langkah 3.3: Penyelarasan Granular Kas & Kalkulasi Biaya (Grup 6)**
   - **Tindakan:**
     - Membungkus `labor-machines.index` dengan `hasPermission('labor_machines.view')`.
     - Membungkus `finance.cash-bank.index` & `settlements` dengan `hasPermission('finance.cash_bank')`.
     - Membungkus `finance.expenses.index` dengan `hasPermission('expenses.view')`.
     - Membungkus `finance.receivables` dan `finance.payables` dengan izin masing-masing.
   - **Hasil:** Akses staf keuangan parsial terlindungi secara presisi.

---

### FASE 4: Verifikasi Komprehensif, Otomasi Collision Test & Dokumentasi 3-Layer [SELESAI / COMPLETED]

1. **Langkah 4.1: Eksekusi Test Script Otomatis**
   - Jalankan `python scratch/detect_active_collisions.py` (Target: 0 Active Collisions).
   - Jalankan `python scratch/inspect_nav_duplicates.py` (Target: 100% Parity 71 vs 71).
   - Jalankan `php -l resources/views/layouts/partials/sidebar.blade.php` (Target: Pass).

2. **Langkah 4.2: Pembaruan Dokumentasi 3-Layer**
   - **Layer 1:** Catat entri `[WORK-2026-09-28-207]` pada `docs/AiWorkHistory.md`.
   - **Layer 2:** Sinkronkan matriks izin di `docs/system/permissions/permission-matrix.md`.
   - **Layer 3:** Perbarui Seksi 4.20 di `docs/SYSTEM_GUIDE.md`.

---

## 3. Matriks Kepatuhan & Checklist Pelaksanaan

| No | Modul / Fitur | Lokasi Berkas | Status | Validasi |
| :---: | :--- | :--- | :---: | :---: |
| 1 | **Isolasi Status Aktif `settings.index`** | `sidebar.blade.php:2311` | ✅ SELESAI | 0 Collision |
| 2 | **Unifikasi Log Audit & Badge Risiko (Opsi B)** | `sidebar.blade.php:885, 2300, 2368` | ✅ SELESAI | Paritas 100% |
| 3 | **Granular Guard Laporan Manajerial** | `sidebar.blade.php:2134, 2200` | ✅ SELESAI | Zero 403 |
| 4 | **Granular Guard Aturan Persetujuan (MAR)** | `sidebar.blade.php:2291, 2356` | ✅ SELESAI | Zero 403 |
| 5 | **Granular Guard Saluran WA & Medsos** | `sidebar.blade.php:1646, 1764` | ✅ SELESAI | Zero 403 |
| 6 | **Granular Guard POS Orders** | `sidebar.blade.php:1008, 1061` | ✅ SELESAI | Zero 403 |
| 7 | **Granular Guard Keuangan & Biaya Mesin** | `sidebar.blade.php:1841, 1995` | ✅ SELESAI | Zero 403 |
| 8 | **Sinkronisasi Dokumentasi 3-Layer** | `AiWorkHistory.md`, `SYSTEM_GUIDE.md` | ✅ SELESAI | Tersinkron |
