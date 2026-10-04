# RENCANA IMPLEMENTASI MASTER: REMEDIASI EKOSISTEM PELANGGAN, CRM LOYALITAS & PUSAT BANTUAN FEEDBACK

**Dokumen Rujukan:** `docs/IMPLEMENTATION_PLAN_CRM_CUSTOMERS_FEEDBACK_REMEDIATION.md`  
**Dokumen Audit Induk:** [`docs/AUDIT_KOMPREHENSIF_CRM_CUSTOMERS_FEEDBACK_7_SKILL.md`](file:///c:/laragon/www/cooca_core/docs/AUDIT_KOMPREHENSIF_CRM_CUSTOMERS_FEEDBACK_7_SKILL.md)  
**Dokumen PRD:** [`docs/prd/PRD-28-CRM-CUSTOMERS-FEEDBACK-REMEDIATION.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-28-CRM-CUSTOMERS-FEEDBACK-REMEDIATION.md)  
**Status Eksekusi:** **READY FOR STAGED SURGICAL EXECUTION (Menunggu Persetujuan Pengguna)**  
**Target Modul:** Hub 4 (`customers`, `crm`) & Hub 6 (`feedback`)  
**Prinsip Eksekusi:** *Surgical, Zero-Regresi, 100% Verified by Automated Tests, No-Panic UI Ergonomics*

---

## 1. STRUKTUR ROADMAP MASTER 10 FASE

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        ROADMAP REMEDIASI 10 FASE SISTEM                                │
├──────────┬──────────────────────────────────────────────────────────────┬──────────────┤
│ Fase 1   │ Patch Multi-Tenant Scope pada Model BugReport & FeatureReq   │ Security     │
│ Fase 2   │ Pengetatan Assertion Tenant Guardrail di Controller & Service│ Anti-Fraud   │
│ Fase 3   │ Penguatan Anti-Lapping Shield, Overpayment & E-Kuitansi WA   │ Finance/CRM  │
│ Fase 4   │ Unifikasi Arsitektur Tab Hub & Deep-Linking URL State        │ Architecture │
│ Fase 5   │ Dynamic Context-Aware Auto-Hiding 20 Sektor Industri         │ Multi-Sector │
│ Fase 6   │ Standardisasi 3-Baris Page Header & Breadcrumb pada Feedback │ UI Consistency│
│ Fase 7   │ Redesain Bento Apple HIG Canvas XXL & Mobile Bottom Sheet    │ Bento HIG    │
│ Fase 8   │ Transformasi Tabel Desktop ke Kartu Responsif Mobile         │ Mobile-First │
│ Fase 9   │ Standardisasi Kamus Dwibahasa (lang/id & lang/en) & JS i18n  │ i18n / l10n  │
│ Fase 10  │ Eksekusi Test Suite 100% Lolos & Update Dokumentasi Layer 2  │ QA & Docs    │
└──────────┴──────────────────────────────────────────────────────────────┴──────────────┘
```

---

## 2. RINCIAN TEKNIS, PREKONDISI, & PROSEDUR SETIAP FASE

### Fase 1: Patch Multi-Tenant Scope pada Model BugReport & FeatureRequest `[COMPLETED]`
- **Status:** **SELESAI (100% Lolos Automated Tests)**
- **Tujuan:** Menjamin seluruh data laporan bug dan usulan fitur otomatis terisolasi per tenant via `BusinessScope`.
- **Berkas yang Dimodifikasi:**
  - [`app/Models/BugReport.php`](file:///c:/laragon/www/cooca_core/app/Models/BugReport.php)
  - [`app/Models/FeatureRequest.php`](file:///c:/laragon/www/cooca_core/app/Models/FeatureRequest.php)
  - [`tests/Feature/FeedbackWorkflowTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/FeedbackWorkflowTest.php)
- **Tindakan Teknis:**
  1. [x] Tambahkan `use App\Models\Traits\BelongsToBusiness;` pada kelas model.
  2. [x] Tambahkan `BelongsToBusiness` pada use statement di dalam body model.
  3. [x] Pastikan kolom `business_id` otomatis terisi saat pembuatan model baru melalui global hook `creating`.
- **Kriteria Verifikasi:** Terverifikasi via `test_bug_reports_and_features_are_isolated_by_belongs_to_business_scope` (`FeedbackWorkflowTest`), query model dan route model binding `{bugReport}` / `{featureRequest}` menolak akses tenant lain dengan HTTP 404/403.

---

### Fase 2: Pengetatan Assertion Tenant Guardrail di Controller & Service `[COMPLETED]`
- **Status:** **SELESAI (100% Lolos Automated Tests)**
- **Tujuan:** Mencegah manipulasi status atau mutasi data lintas tenant via parameter HTTP binding.
- **Berkas yang Dimodifikasi:**
  - [`app/Http/Controllers/Web/Crm/CrmWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Crm/CrmWebController.php)
  - [`app/Http/Controllers/Web/CustomerWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/CustomerWebController.php)
  - [`app/Domain/Crm/LoyaltyService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Crm/LoyaltyService.php)
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
- **Tindakan Teknis:**
  1. [x] Pasang `abort_unless($customer->business_id === $business->id, 403);` di awal method `pointHistories` dan `recordCreditPayment`.
  2. [x] Pasang `abort_unless($voucher->business_id === $business->id, 403);` di awal method `toggleVoucher`.
  3. [x] Perkuat validasi kode voucher pada `storeVoucher`: `Rule::unique('vouchers', 'code')->where('business_id', $business->id)`.
  4. [x] Perkuat validasi kode pelanggan pada `CustomerWebController@store` dan `@update`: `Rule::unique('customers', 'code')->where('business_id', $business->id)`.
  5. [x] Tambahkan assertion tenant guardrail pada `LoyaltyService` (`awardPointsForOrder`, `redeemPointsForOrder`, `recordCustomerCreditCharge`, `recordCustomerCreditPayment`).
- **Kriteria Verifikasi:** Terverifikasi via `test_crm_controller_tenant_guardrail_assertions` dan `test_customer_code_uniqueness_scoped_to_business` di `CrmAndCustomerComprehensiveSecurityTest` (status 403/404 saat request ID lintas tenant).

---

### Fase 3: Penguatan Anti-Lapping Shield, Batas Overpayment & E-Kuitansi WhatsApp `[COMPLETED]`
- **Status:** **SELESAI (100% Lolos Automated Tests)**
- **Tujuan:** Melindungi keuangan bisnis dari penggelapan kasir (*lapping*), mencegah pembayaran kasbon melebihi sisa hutang, dan mengirimkan bukti digital ke pelanggan.
- **Berkas yang Dimodifikasi:**
  - [`app/Http/Controllers/Web/Crm/CrmWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Crm/CrmWebController.php)
  - [`app/Domain/Crm/LoyaltyService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Crm/LoyaltyService.php)
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
- **Tindakan Teknis:**
  1. [x] Di `CrmWebController@recordCreditPayment`, batasi validasi input:
     `'amount' => ['required', 'numeric', 'min:1', "max:{$customer->current_credit_balance}"]` serta tolak saldo piutang $\le 0$.
  2. [x] Di `LoyaltyService@recordCustomerCreditPayment`:
     - Tolak overpayment di level domain dengan melempar `\InvalidArgumentException`.
     - Perbarui saldo `current_credit_balance` secara atomik di dalam `DB::transaction`.
     - Catat `CustomerCreditTransaction::create` (tipe `payment`).
     - Sinkronkan buku kas aktif dengan `CashTransaction::create` (tipe `in`, kategori `customer_credit_repayment`).
     - Catat `AuditLog::create` immutable lengkap dengan snapshot `payload_after`.
  3. [x] Tambahkan method `generateCreditPaymentWhatsAppReceiptUrl(Customer $customer, CustomerCreditTransaction $transaction, ?string $phone = null): string` pada `LoyaltyService` untuk merender tautan nota pelunasan digital resmi berformat `wa.me/` langsung ke WhatsApp pelanggan.
- **Kriteria Verifikasi:** Terverifikasi via `test_credit_payment_overpayment_is_strictly_rejected_by_controller_and_service` dan `test_credit_payment_returns_whatsapp_receipt_url_for_anti_lapping_shield` di `CrmAndCustomerComprehensiveSecurityTest` (status 422 saat overpayment dan URL wa.me sah).

---

### Fase 4: Unifikasi Arsitektur Tab Hub & Deep-Linking URL State [COMPLETED]
- **Tujuan:** Mengeliminasi duplikasi view 1.182 baris pada `customers/index.blade.php` dan menyatukan navigasi tab CRM.
- **Berkas yang Dimodifikasi:**
  - [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php)
  - [`app/Http/Controllers/Web/CustomerWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/CustomerWebController.php)
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
- **Tindakan Teknis:**
  1. [x] Rampingkan `customers/index.blade.php`: fokuskan sebagai direktori pelanggan komersial utama (dari 1.182 baris dirampingkan menjadi ~450 baris bersih), hapus duplikasi tabel member dan voucher yang sudah memiliki modul mandiri di `crm/members.blade.php` dan `crm/vouchers.blade.php`.
  2. [x] Pertahankan deep-linking parameter URL: `CustomerWebController@index` melakukan HTTP redirect otomatis jika menerima `?tab=members` ke `route('crm.members.index')` dan `?tab=vouchers` ke `route('crm.vouchers.index')`.
  3. [x] Navigasi kanonikal terpadu via `<x-module-tabs module="crm" />` yang didukung penuh oleh `NavigationRegistry` (Buku Pelanggan, Member & Tingkatan, Voucher Promo).
  4. [x] Pastikan pagination links (`$customers->links()`) membawa query string aktif (`withQueryString()`).
- **Kriteria Verifikasi:** Berpindah tab mempertahankan URL state saat reload halaman tanpa duplikasi baris HTML. Terverifikasi 100% lolos via `CustomerWebFeatureTest` (5/5) dan `test_customer_index_deep_links_tab_redirects` serta `test_crm_submodule_pages_render_cleanly_with_canonical_tabs` di `CrmAndCustomerComprehensiveSecurityTest`.


---

### Fase 5: Dynamic Context-Aware Auto-Hiding 20 Sektor Industri [COMPLETED]
- **Tujuan:** Menyembunyikan elemen B2B korporat pada bisnis non-B2B, dan menampilkan atribut kendaraan untuk sektor Bengkel.
- **Berkas yang Dimodifikasi:**
  - [`database/migrations/2026_10_01_000002_add_vehicle_fields_to_customers_table.php`](file:///c:/laragon/www/cooca_core/database/migrations/2026_10_01_000002_add_vehicle_fields_to_customers_table.php)
  - [`app/Models/Customer.php`](file:///c:/laragon/www/cooca_core/app/Models/Customer.php)
  - [`app/Http/Controllers/Web/CustomerWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/CustomerWebController.php)
  - [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php)
  - [`resources/views/app/crm/members.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/members.blade.php)
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
- **Tindakan Teknis:**
  1. [x] Bungkus input NPWP, Termin Tempo, dan Nama Perusahaan dengan `@if ($canB2b)` berdasarkan module `b2b_sales`.
  2. [x] Tambahkan migrasi skema basis data dan input Nomor Polisi (Nopol), Merk/Model Kendaraan, dan Odometer (KM) dengan `@if ($isWorkshop)` berdasarkan module `service_workshop`.
  3. [x] Bungkus KPI Voucher & Member dengan `@if ($canCrmLoyalty)` berdasarkan module `crm_loyalty`.
  4. [x] Dukung pencarian pelanggan berdasarkan nomor polisi dan model kendaraan pada backend controller `CustomerWebController@index`.
- **Kriteria Verifikasi:** Akun dengan template kafe/resto (`fnb_cafe`) tidak melihat isian NPWP/Termin Tempo; akun bengkel (`service_workshop`) melihat field Nopol kendaraan dan data tersimpan dengan benar ke database. Terverifikasi 100% lolos via `test_customer_ui_auto_hides_b2b_fields_for_fnb_or_non_b2b_business` dan `test_customer_ui_shows_and_persists_vehicle_fields_for_workshop_business` di `CrmAndCustomerComprehensiveSecurityTest`.

---

### Fase 6: Standardisasi 3-Baris Page Header & Breadcrumb pada Modul Feedback [COMPLETED]
- **Tujuan:** Menyelaraskan seluruh tampilan halaman feedback dengan standar Apple HIG 3-Baris.
- **Berkas yang Dimodifikasi:**
  - [`app/Support/Navigation/NavigationRegistry.php`](file:///c:/laragon/www/cooca_core/app/Support/Navigation/NavigationRegistry.php)
  - [`resources/views/app/feedback/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/index.blade.php)
  - [`resources/views/app/feedback/create.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/create.blade.php)
  - [`resources/views/app/feedback/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/show.blade.php)
  - [`tests/Feature/FeedbackWorkflowTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/FeedbackWorkflowTest.php)
- **Tindakan Teknis:**
  1. [x] Gunakan `<x-module-header module="feedback" ...>` dengan 3-baris Apple HIG (Breadcrumbs navigasi, Judul Halaman + Lencana Status, Deskripsi Konteks) pada `index.blade.php`, `create.blade.php`, dan `show.blade.php`.
  2. [x] Sisipkan navigasi kanonikal `<x-module-tabs module="feedback" />` yang disinkronkan via `NavigationRegistry` antar tab Laporan Kendala (Bugs) dan Request Fitur (Features).
  3. [x] Sediakan breadcrumbs hirarkis yang dapat diklik (`Dashboard` $\rightarrow$ `Dukungan Produk & Feedback` $\rightarrow$ `Laporan / Tiket Aktif`).
  4. [x] Standarkan palet warna frosted glass (`bg-white/75 dark:bg-[#1C1C1E]/75 border-black/[0.06] dark:border-white/[0.08]`) dan tata letak bento card terpusat (`max-w-4xl` & `max-w-5xl`).
- **Kriteria Verifikasi:** Seluruh sub-halaman feedback memiliki tata letak header seragam, breadcrumb navigasi yang dapat diklik, tab kanonikal aktif, dan terverifikasi lolos 100% via `test_feedback_pages_render_3_row_apple_hig_header_tabs_and_breadcrumbs` di `FeedbackWorkflowTest`.

---

### Fase 7: Redesain Bento Apple HIG Canvas XXL & Mobile Bottom Sheet Form [COMPLETED]
- **Tujuan:** Menghilangkan form modal sempit (`max-w-md`), mengadopsi Canvas XXL 2-kolom lapang di desktop, dan Bottom Sheet di ponsel.
- **Berkas yang Dimodifikasi:**
  - [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php)
  - [`resources/views/app/crm/members.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/members.blade.php)
- **Tindakan Teknis:**
  1. [x] Ubah kontainer modal tambah/edit pelanggan menjadi `w-full max-w-5xl rounded-[28px]` pada layar `lg:` ke atas dengan grid 2-kolom.
  2. [x] Pada layar ponsel (`< lg`), terapkan `fixed inset-x-0 bottom-0 rounded-t-[28px] max-h-[92vh] overflow-y-auto` dengan indikator drag pill handle (`w-12 h-1.5 rounded-full`).
  3. [x] Terapkan prinsip 3 input pokok ramah pengguna usia 40–65 tahun (Nama, WhatsApp, Perusahaan/Nopol) dengan pengelompokan visual rapi dan font input minimal 16px di ponsel untuk mencegah auto-zoom iOS Safari.
  4. [x] Terapkan standar Apple HIG Bottom Sheet & Desktop modal dialog pada modal Pelunasan Kasbon (`max-w-xl`) dan Riwayat Poin Belanja (`max-w-2xl`) di modul CRM Member.
- **Kriteria Verifikasi:** Form modal di desktop tampil lapang 2-kolom tanpa sesak; di ponsel tampil elegan dari dasar layar (*bottom sheet*), terverifikasi lolos 100% pada 23 tes regresi fitur dan keamanan (165 assertions).

---

### Fase 8: Transformasi Tabel Desktop ke Kartu Responsif Mobile (Thumb Zone) [COMPLETED]
- **Tujuan:** Menghilangkan total scroll horizontal tabel pada layar ponsel sempit (320px–390px).
- **Berkas yang Dimodifikasi:**
  - [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php)
  - [`resources/views/app/crm/members.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/members.blade.php)
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
- **Tindakan Teknis:**
  1. [x] Bungkus tag `<table>` dengan kontainer desktop `hidden lg:block overflow-x-auto`.
  2. [x] Buat kontainer kartu mobile `block lg:hidden divide-y divide-black/[0.05] dark:divide-white/[0.06]`.
  3. [x] Tempatkan tombol aksi cepat (Chat WhatsApp, Pelunasan Kasbon, Profil, Edit, Hapus) di zona ibu jari (*thumb zone*) dengan tinggi minimal 44px (`h-11`) dan padding bawah aman `pb-24 lg:pb-16`.
- **Kriteria Verifikasi:** Pengujian pada resolusi ponsel sempit menunjukkan 0 horizontal scrollbar dan seluruh aksi dapat dijangkau nyaman satu tangan di zona ibu jari. Terverifikasi lolos 100% pada 24 tes regresi fitur dan keamanan (177 assertions).

---

### Fase 9: Standardisasi Kamus Dwibahasa (lang/id & lang/en) & JS i18n [COMPLETED]
- **Tujuan:** Memastikan 100% string antarmuka bebas teks mentah dan mendukung lokalisasi penuh ID & EN.
- **Berkas yang Dimodifikasi:**
  - [`lang/id/customers.php`](file:///c:/laragon/www/cooca_core/lang/id/customers.php) & [`lang/en/customers.php`](file:///c:/laragon/www/cooca_core/lang/en/customers.php)
  - [`lang/id/crm.php`](file:///c:/laragon/www/cooca_core/lang/id/crm.php) & [`lang/en/crm.php`](file:///c:/laragon/www/cooca_core/lang/en/crm.php)
  - [`lang/id/feedback.php`](file:///c:/laragon/www/cooca_core/lang/id/feedback.php) & [`lang/en/feedback.php`](file:///c:/laragon/www/cooca_core/lang/en/feedback.php)
  - [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php)
  - [`resources/views/app/crm/members.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/members.blade.php)
  - [`resources/views/app/crm/vouchers.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/vouchers.blade.php)
  - [`resources/views/app/feedback/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/index.blade.php)
  - [`resources/views/app/feedback/create.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/create.blade.php)
  - [`resources/views/app/feedback/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/show.blade.php)
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
- **Tindakan Teknis:**
  1. [x] Sinkronkan 100% seluruh pasangan key di kamus `id` dan `en` (`customers.php`, `crm.php`, `feedback.php`) dengan 0 disparitas key.
  2. [x] Gantikan seluruh string mentah di Blade dengan `{{ __('domain.key') }}` dan raw emoji dieliminasi secara total.
  3. [x] Injeksi string dinamis JS via `window.COOCA_I18N` dan `window.COOCA_LOCALE` pada view customers dan CRM members.
- **Kriteria Verifikasi:** Mengubah locale sistem ke `en` menerjemahkan 100% judul, tabel, modal, status badge, dan pesan notifikasi. Terverifikasi lolos 100% pada suite `test_i18n_dictionaries_integrity` dan `test_customers_and_crm_bilingual_locale_rendering` (16 tests, 127 assertions).

---

### Fase 10: Eksekusi Test Suite 100% Lolos & Pembaruan Dokumentasi Layer 2
- **Tujuan:** Memvalidasi seluruh perubahan tanpa cacat dan mendokumentasikan hasil pekerjaan pada sistem dokumentasi 3-layer.
- **Berkas yang Dimodifikasi:**
  - [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php)
  - [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md)
  - [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md)
  - [`docs/system/workflows/customer-crm-and-feedback-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/customer-crm-and-feedback-flow.md)
- **Tindakan Teknis:**
  1. Jalankan pengujian otomatis: `php artisan test tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`.
  2. Pastikan hasil pengujian `PASSED` 100% dengan 0 failure dan 0 error.
  3. Catat riwayat pekerjaan pada `docs/AiWorkHistory.md` dengan 7 komponen wajib.
- **Kriteria Verifikasi:** Seluruh assertion lolos pengujian CI/CD dan dokumentasi sistem mutakhir.

---

## 3. MATRIKS LENGKAP FILE TERDAMPAK

| No | Path Berkas | Peran dalam Sistem | Lingkup Perubahan |
| :---: | :--- | :--- | :--- |
| **01** | [`app/Models/BugReport.php`](file:///c:/laragon/www/cooca_core/app/Models/BugReport.php) | Model Tiket Bug | Tambah trait `BelongsToBusiness` |
| **02** | [`app/Models/FeatureRequest.php`](file:///c:/laragon/www/cooca_core/app/Models/FeatureRequest.php) | Model Request Fitur | Tambah trait `BelongsToBusiness` |
| **03** | [`app/Http/Controllers/Web/Crm/CrmWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Crm/CrmWebController.php) | Web Controller CRM | Tenant scoping, validasi overpayment kasbon, link WA |
| **04** | [`app/Domain/Crm/LoyaltyService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Crm/LoyaltyService.php) | Domain Service CRM | Generator e-kuitansi WA, sinkronisasi buku kas & audit |
| **05** | [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php) | View Master Pelanggan | Eliminasi duplikasi, Canvas XXL, kartu mobile, auto-hiding |
| **06** | [`resources/views/app/crm/members.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/members.blade.php) | View Member CRM | Bento Canvas XXL, kartu mobile, pembersihan i18n |
| **07** | [`resources/views/app/crm/vouchers.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/crm/vouchers.blade.php) | View Voucher CRM | i18n penuh, microcopy penenang jiwa, double submit |
| **08** | [`resources/views/app/feedback/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/index.blade.php) | View Index Feedback | Header 3-baris terpadu, i18n, Bento HIG dual-theme |
| **09** | [`resources/views/app/feedback/create.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/create.blade.php) | View Form Feedback | Standardisasi `<x-module-header>`, i18n penuh |
| **10** | [`resources/views/app/feedback/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/feedback/show.blade.php) | View Detail Feedback | Header 3-baris terpadu, attachment viewer, i18n |
| **11** | [`lang/id/customers.php`](file:///c:/laragon/www/cooca_core/lang/id/customers.php) & [`lang/en/customers.php`](file:///c:/laragon/www/cooca_core/lang/en/customers.php) | Kamus Bahasa Pelanggan | Penambahan key pelunasan kasbon, Nopol, dan validasi |
| **12** | [`lang/id/crm.php`](file:///c:/laragon/www/cooca_core/lang/id/crm.php) & [`lang/en/crm.php`](file:///c:/laragon/www/cooca_core/lang/en/crm.php) | Kamus Bahasa CRM | Sinkronisasi pesan sukses, e-kuitansi WA, dan tiering |
| **13** | [`tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php) | Test Suite Otomatis | Penambahan test case overpayment, isolasi feedback, auto-hiding |
| **14** | [`docs/system/workflows/customer-crm-and-feedback-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/customer-crm-and-feedback-flow.md) | Layer 2 Workflow Spec | Pembaruan dokumen arsitektur dan simpul eksekusi |

---

## 4. RENCANA PENGUJIAN OTOMATIS & VERIFIKASI

Pengujian regresi dijalankan menggunakan PHPUnit/Laravel Test Runner:
```bash
php artisan test tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php
```

### Skenario Uji Kunci
1. **`test_credit_payment_cannot_exceed_current_debt_balance`**:
   Kasir menginput pelunasan Rp 300.000 pada pelanggan berhutang Rp 200.000 $\rightarrow$ Server menolak dengan response HTTP 422 dan pesan validasi rapi.
2. **`test_credit_repayment_generates_whatsapp_receipt_payload`**:
   Pelunasan kasbon berhasil $\rightarrow$ Memeriksa ketersediaan redirect session `wa_receipt_url` yang memuat parameter nama pelanggan, nomor nota, nominal, dan tautan e-kuitansi.
3. **`test_feedback_models_enforce_business_scope_isolation`**:
   Panggilan `BugReport::all()` di bawah session Bisnis A hanya memuat tiket Bisnis A $\rightarrow$ Tidak ada tiket Bisnis B yang bocor.
4. **`test_customer_views_dynamic_module_auto_hiding`**:
   Rendering view pelanggan untuk bisnis non-B2B tidak memuat string "NPWP" dan "Termin Tempo".
5. **`test_customers_and_crm_full_i18n_localization`**:
   Memastikan tidak ada string mentah Indonesia saat environment locale diatur ke `'en'`.

---

## 5. RENCANA ROLLBACK & SAFETY GATES

Jika terjadi anomali selama pengujian:
1. **Safety Gate Interaktif**: Setiap fase hanya dieksekusi secara bertahap setelah kode fase sebelumnya terbukti lolos linting (`php -l`) dan test unit.
2. **Rollback Git Bersih**: Setiap fase dapat di-revert secara instan via `git checkout -- <file>` tanpa merusak migrasi database karena tidak ada alter skema destruktif.
3. **Integritas Database**: Tidak ada penghapusan kolom atau tabel basis data historis; data pelanggan dan transaksi masa lalu dijamin 100% aman tersimpan.

---
*Dokumen Rencana Implementasi Master ini telah siap dieksekusi secara terarah dan teruji.*
