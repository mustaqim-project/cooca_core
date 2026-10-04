# PRD-28: Remediasi Komprehensif Ekosistem Pelanggan, CRM Loyalitas & Pusat Bantuan Feedback

**Dokumen Rujukan:** `docs/prd/PRD-28-CRM-CUSTOMERS-FEEDBACK-REMEDIATION.md`  
**Dokumen Audit Induk:** [`docs/AUDIT_KOMPREHENSIF_CRM_CUSTOMERS_FEEDBACK_7_SKILL.md`](file:///c:/laragon/www/cooca_core/docs/AUDIT_KOMPREHENSIF_CRM_CUSTOMERS_FEEDBACK_7_SKILL.md)  
**Dokumen Rencana Eksekusi:** [`docs/IMPLEMENTATION_PLAN_CRM_CUSTOMERS_FEEDBACK_REMEDIATION.md`](file:///c:/laragon/www/cooca_core/docs/IMPLEMENTATION_PLAN_CRM_CUSTOMERS_FEEDBACK_REMEDIATION.md)  
**Status:** **PROPOSED & READY FOR IMPLEMENTATION**  
**Versi:** 2.0.0 (Comprehensive Multi-Skill Alignment)  
**Tanggal Rilis:** 2026-10-01  
**Aktor Sistem:** Superadmin, Business Owner, Manajer Toko, Kasir POS, Customer

---

## 1. Executive Summary & Problem Statement

### 1.1 Latar Belakang
Modul Pelanggan (`customers`), CRM Loyalitas (`crm`), dan Pusat Bantuan Feedback (`feedback`) merupakan pilar interaksi sentral dalam operasional harian merchant UMKM dan relasi dukungan platform SaaS COOCA. Audit komprehensif 7 dimensi menemukan beberapa area perbaikan struktural:
1. Terjadi duplikasi markup tabel dan modal antara `customers/index.blade.php` dan `crm/members.blade.php`/`crm/vouchers.blade.php`.
2. Validasi pelunasan kasbon belum membatasi nominal maksimal sebesar sisa piutang aktif, dan kuitansi digital belum otomatis dikirimkan ke WhatsApp pelanggan (celah fraud *lapping* kasir).
3. Model `BugReport` dan `FeatureRequest` belum menggunakan trait `BelongsToBusiness`.
4. Field B2B korporat (NPWP, Termin Tempo) muncul pada bisnis retail/kuliner, sedangkan bisnis Bengkel (`service_workshop`) belum memiliki field Nopol kendaraan.
5. Tabel data di desktop belum bertransformasi menjadi kartu ringkas pada ponsel mobile 360px.
6. 100% teks antarmuka pada view CRM dan Customers belum terintegrasi ke kamus bahasa `lang/id/` dan `lang/en/`.

### 1.2 Tujuan Utama (Core Objectives)
1. **Zero Multi-Tenant Leakage**: Menjamin isolasi penuh pada model tiket feedback via `BelongsToBusiness`.
2. **Anti-Lapping Shield & Financial Integrity**: Menutup celah overpayment piutang kasbon, mengintegrasikan auto-journaling kas, dan menyediakan link kuitansi WhatsApp otomatis.
3. **Bento Apple HIG v2.0 & Canvas XXL**: Menghadirkan modal form lapang 2-kolom (`max-w-5xl`) di desktop dan Bottom Sheet responsif di ponsel.
4. **Mobile-First Touch Ergonomics**: Menghilangkan total horizontal scroll pada layar ponsel sempit dengan transformasi tabel ke kartu ringkas.
5. **Dynamic Context-Aware Auto-Hiding**: Mengaktifkan seleksi visibilitas field berbasis modul industri aktif (`MODULE_B2B_SALES`, `MODULE_SERVICE_WORKSHOP`, `MODULE_CRM_LOYALTY`).
6. **Full-Stack Localization (i18n)**: Menerapkan 100% kamus dwibahasa ID & EN pada seluruh antarmuka dan flash messages.

---

## 2. Business Rules & Financial Guardrails

- **[BR-CRM-01] Integritas Pembukuan Pelunasan Kasbon**:
  Setiap pembayaran kasbon (`recordCustomerCreditPayment`) wajib secara atomik:
  1. Mengurangi `current_credit_balance` pelanggan.
  2. Mencatat mutasi pada `CustomerCreditTransaction`.
  3. Mencatat arus kas masuk pada `CashTransaction` (tipe `in`, kategori `customer_credit_repayment`).
  4. Mencatat `AuditLog` dengan snapshot saldo sebelum dan sesudah.
  5. Menolak input nominal yang melebihi saldo hutang aktif (`amount <= current_credit_balance`).
- **[BR-CRM-02] E-Kuitansi Digital WhatsApp (Anti-Lapping)**:
  Sistem wajib men-generate tautan kuitansi WhatsApp resmi saat kasbon dilunasi, memuat nama pelanggan, nomor kuitansi, tanggal, nominal diterima, dan sisa hutang saat ini.
- **[BR-CRM-03] Voucher Diskon Kasir Multi-Tenant**:
  Kode kupon voucher wajib unik di dalam satu `business_id` aktif. Diskon persentase dibatasi maksimal 100% dan wajib mematuhi aturan minimum belanja (`min_order_amount`).
- **[BR-CRM-04] Poin Loyalitas & Evaluasi Tier**:
  Poin loyalitas diperoleh otomatis dari pesanan kasir POS (Rp 10.000 = 1 poin) dan dapat ditukar diskon (1 poin = Rp 100). Evaluasi tingkatan member berlangsung otomatis: Bronze (< 1jt), Silver ($\ge$ 1jt), Gold ($\ge$ 5jt), Platinum ($\ge$ 15jt).
- **[BR-CRM-05] Proteksi Data Historis (No-Panic Soft Deletes)**:
  Penghapusan data kontak pelanggan wajib menggunakan `SoftDeletes`. Riwayat faktur, kuitansi kasbon, dan pesanan historis tetap abadi di database.

---

## 3. Spesifikasi Fungsional Antarmuka (UI/UX)

### 3.1 Pusat Pelanggan (`/customers`)
- **Header 3-Baris Standar**:
  - Overline: `CRM & Direktori Kontak`
  - Title H1: `Pusat Pelanggan & Klien Komersial`
  - Subtitle 1-baris padat
  - Tombol Aksi Kanan: `+ Tambah Pelanggan` (48px)
- **Bento Hero Metrics (4 Tile)**: Total Pelanggan, Klien B2B, Rata-rata Tempo (Hari), Total Piutang Kasbon.
- **Tabel Desktop & Kartu Mobile**:
  - Desktop ($ \ge 1024px $): Tabel 5-kolom dengan tipografi SF Pro `tabular-nums`.
  - Mobile ($ < 1024px $): Kartu Bento terpisah dengan tombol aksi cepat (Chat WA, Bayar Kasbon, Profil) di zona ibu jari.
- **Modal-First Canvas XXL**: Form tambah/edit pelanggan berukuran `max-w-5xl rounded-[28px]` 2-kolom di desktop, dan Bottom Sheet di mobile.

### 3.2 Member CRM & Voucher Promo (`/crm/members` & `/crm/vouchers`)
- **Tabel Member**: Klasifikasi tier dengan lencana warna Apple HIG (Bronze amber, Silver slate, Gold yellow, Platinum purple).
- **Modal Pelunasan Kasbon**: Format ribuan otomatis (`Intl.NumberFormat`), pecahan cepat (25%, 50%, 100% Lunas), dan microcopy penenang jiwa.
- **Grid Kupon Promo**: Kartu voucher squircle continuo dengan fitur 1-klik salin kode, toggle status aktif/nonaktif, dan visual kuota pemakaian.

### 3.3 Pusat Bantuan & Tiket Feedback (`/feedback/bugs` & `/feedback/features`)
- **Tampilan Terpadu Apple HIG**: Mendukung Light & Dark mode secara penuh dengan frosted glass Vibrancy.
- **Header 3-Baris & Breadcrumb**: Dilengkapi breadcrumb navigasi dan tab submodule terpadu (`bugs` & `features`).
- **Progress Tracking Stepper**: Visualisasi progres 0–100% dengan status terstandarisasi (`open`, `triaged`, `in_progress`, `resolved`, `closed`).
- **Attachment Viewer**: Penampil pratinjau thumbnail file dan tombol unduh dokumen aman.

---

## 4. Matriks Hak Akses Pengguna (RBAC)

| Peran (Role) | Lihat Pelanggan | Tambah / Edit Pelanggan | Hapus Pelanggan | Lihat CRM / Poin | Kelola Voucher / Kasbon | Buat Tiket Feedback |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Superadmin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (Kelola) |
| **Business Owner** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Store Manager** | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ |
| **Kasir POS** | ✅ | ✅ (Quick Add) | ❌ | ✅ (View Point) | ❌ | ✅ |
| **Mekanik / Staf** | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 5. Non-Functional Requirements & Standar Kualitas

- **Performa & Kecepatan**: Sub-100ms response time; pengiriman e-kuitansi via browser client wa.me atau queue asinkron.
- **Mobile Touch Targets**: Minimal 44x44px untuk elemen umum; 48–52px untuk tombol primer dan aksi kasir.
- **Anti Auto-Zoom iOS**: Ukuran font input mobile minimal 16px (`text-[16px] sm:text-[14px]`).
- **Aksessibilitas WCAG AA**: Rasio kontras teks minimal 4.5:1 terhadap latar belakang frosted glass.
- **Dwibahasa Penuh (i18n)**: Seluruh label, pesan validasi, dan flash alert tersedia dalam Bahasa Indonesia (`id`) dan English (`en`).

---
*Dokumen PRD ini menjadi acuan spesifikasi fungsional dan teknis resmi Cooca ERP.*
