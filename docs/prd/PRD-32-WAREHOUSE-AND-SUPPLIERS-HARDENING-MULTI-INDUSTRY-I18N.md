# PRD-32: Hardening Keamanan, Proteksi IDOR, Antarmuka Sadar Konteks 20 Industri, Multi-Language i18n & Multi-Sheet Excel Engine pada Modul Gudang dan Pemasok

> **Status:** PROPOSED & READY FOR APPROVAL  
> **Versi:** 1.0  
> **Penanggung Jawab:** Security Engineer, Senior Laravel Architect, Inclusive UI/UX Engineer, QA & Automation Specialist  
> **Modul Terkait:** `resources/views/app/warehouse/`, `resources/views/app/suppliers/`, `WarehouseWebController`, `SupplierWebController`, `Location`, `Supplier`, `InventoryStock`, `StockMovement`  
> **Dokumen Terkait:** [`docs/system/audits/warehouse-and-suppliers-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/warehouse-and-suppliers-comprehensive-audit.md), [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

Modul **Cabang & Gudang Logistik (`/warehouse`)** dan **Direktori Pemasok & Vendor (`/suppliers`)** merupakan fondasi operasional dari Hub Katalog, Logistik, dan Pengadaan (AP) pada ekosistem COOCA ERP & POS. 

Berdasarkan audit komprehensif 8 dimensi pada 4 Oktober 2026, ditemukan beberapa area kritis yang perlu segera diperbaiki:
1. **Celah IDOR Lintas Tenant pada Model Supplier:** SQL operator precedence `OR` pada `Supplier::resolveRouteBinding()` dapat membocorkan data vendor antar tenant saat diakses via slug.
2. **Pelanggaran Zero Native Alert/Confirm:** Penggunaan `confirm()` browser native pada persetujuan/penolakan penyesuaian stok di `warehouse/show.blade.php` melanggar standar Bento Apple HIG.
3. **Pelanggaran Modal-First Bento XXL pada Supplier:** Modal form tambah/edit pemasok menggunakan layout `max-w-lg` 1-kolom sempit.
4. **Kehilangan State Tab (Tab Desynchronization):** Filter tab tipe gudang tidak tersinkronisasi ke URL parameter (`?type=...`), dan halaman detail gudang mengalami *excessive vertical scroll* tanpa tab internal.
5. **Kebocoran Fitur & Terminologi Lintas Industri:** Tombol "Katalog Bahan" muncul di industri ritel/jasa yang tidak memakai modul resep BOM, dan label `central_kitchen` tidak adaptif terhadap industri manufaktur atau kontraktor.
6. **Pelanggaran 100% Zero Hardcoded Text:** Seluruh antarmuka masih menggunakan string mentah Bahasa Indonesia.
7. **Ketiadaan Mesin Ekspor Microsoft Excel (XLSX) Multi-Sheet:** Belum tersedia ekspor dua bagian (Overview KPI + Granular Movement Ledger) berstandar Bento Apple HIG.

PRD ini merumuskan spesifikasi teknis dan kebutuhan fungsional untuk menyelesaikan seluruh isu di atas secara tuntas.

---

## 2. Ruang Lingkup Proyek (Scope & Non-Scope)

### Dalam Cakupan (In-Scope):
1. **Hotfix Keamanan Model Binding:** Memperbaiki klausul closure group pada `Supplier::resolveRouteBinding()` agar 100% kebal IDOR lintas tenant.
2. **Eliminasi Native Alert/Confirm:** Mengganti dialog konfirmasi browser native di `warehouse/show.blade.php` dengan Modal Dialog Bento Apple HIG / `AppAlert.confirm()`.
3. **Upgrade Bento Modal XXL 2-Kolom:** Mengubah modal tambah & edit supplier di `suppliers/index.blade.php` menjadi layout XXL 2-kolom lapang (`max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl`).
4. **Standarisasi Arsitektur Tab & URL Deep-Linking:**
   - Menambahkan deep-linking query URL `?type=all|outlet|warehouse` pada `warehouse/index.blade.php`.
   - Mengelompokkan `warehouse/show.blade.php` ke dalam 4 Underline Tabs (`?tab=stocks|receipts|movements|approvals`).
5. **Penegakan Auto-Hiding & Terminologi 20 Industri:**
   - Menyembunyikan tombol "Katalog Bahan" jika `$business->isModuleEnabled('recipe_bom') === false`.
   - Menyesuaikan label tipe lokasi `central_kitchen` sesuai template bisnis (F&B: Dapur Pusat, Manufaktur: Pabrik/Workshop, Kontraktor: Basecamp Proyek).
6. **Ekstraksi Multi-Bahasa 100% (i18n & l10n):**
   - Menambahkan key translatable lengkap di `lang/id/warehouse.php`, `lang/en/warehouse.php`, `lang/id/purchasing.php`, `lang/en/purchasing.php`, `lang/id/inventory.php`, `lang/en/inventory.php`.
   - Mengganti seluruh hardcoded text di view Blade dan controller flash messages.
7. **Mesin Ekspor Microsoft Excel (XLSX) Multi-Sheet:**
   - Menyediakan endpoint dan controller logic untuk ekspor XLSX dua sheet (Executive Overview + Detailed Ledger) dengan formula dinamis `=SUM()`.

### Di Luar Cakupan (Non-Scope):
- Mengubah struktur kolom fisik tabel basis data `locations` atau `suppliers` (skema DB sudah mendukung).
- Mengubah mekanisme penghitungan FIFO / Average Costing pada domain kalkulasi HPP inti.

---

## 3. Kebutuhan Fungsional (Functional Requirements) & Skenario Gherkin

### FR-01: Multi-Tenant IDOR Protection on Route Model Binding
- **Aktor:** Semua Pengguna Sistem
- **Kebutuhan:** Model `Supplier` wajib membatasi pencarian `id` atau `slug` di dalam scope tenant aktif.
```gherkin
Scenario: Pengguna mencoba mengakses slug supplier milik tenant lain
  Given User terautentikasi pada Tenant A (business_id = "tenant-A")
  And Terdapat supplier "PT Vendor Global" milik Tenant B dengan slug "pt-vendor-global"
  When User mengakses URL "/suppliers/pt-vendor-global" atau mengirim request PUT/DELETE
  Then Sistem melempar HTTP 404 Not Found
  And Sistem TIDAK menampilkan atau memodifikasi data milik Tenant B
```

### FR-02: Dynamic Context-Aware Auto-Hiding & Adaptasi Industri
- **Aktor:** Pengguna di 20 Sektor Industri
- **Kebutuhan:** Fitur bahan baku resep BOM hanya muncul jika modul aktif; label lokasi menyesuaikan industri.
```gherkin
Scenario: Pengguna retail minimarket membuka halaman pemasok
  Given Bisnis aktif menggunakan template "retail_reseller" dengan modul recipe_bom nonaktif
  When Pengguna membuka halaman "/suppliers"
  Then Tombol "Katalog Bahan" otomatis tersembunyi dari toolbar header
  And Kolom tabel menampilkan terminologi "Item Pasokan"

Scenario: Pengguna industri konveksi membuka halaman gudang
  Given Bisnis aktif menggunakan template "mfg_garment"
  When Pengguna membuka halaman "/warehouse"
  Then Lokasi bertipe "central_kitchen" berlabel "Pabrik / Workshop Produksi"
```

### FR-03: Zero Native Alert/Confirm Compliance
- **Aktor:** Business Owner / Supervisor
- **Kebutuhan:** Persetujuan atau penolakan pengajuan stok bernilai tinggi wajib menggunakan dialog Apple HIG tanpa native browser alert.
```gherkin
Scenario: Supervisor menyetujui pengajuan penyesuaian stok bernilai tinggi
  Given Terdapat pengajuan penyesuaian stok berstatus "pending_approval"
  When Supervisor menekan tombol "Setujui Penyesuaian"
  Then Muncul modal konfirmasi Apple HIG dengan rincian selisih dan estimasi nilai rupiah
  When Supervisor mengonfirmasi di modal tersebut
  Then Request POST dikirimkan, stok dimutasi, dan auto-journal diterbitkan
```

### FR-04: Multi-Language 100% Translatability (ID & EN)
- **Aktor:** Pengguna Dwibahasa
- **Kebutuhan:** 100% teks di 10 domain wajib translatable via helper `{{ __('...') }}`.
```gherkin
Scenario: Pengguna beralih ke Bahasa Inggris
  Given Pengguna memilih bahasa "English (en)"
  When Pengguna membuka halaman "/warehouse" dan "/suppliers"
  Then Judul halaman, kartu KPI, header tabel, form input, modal, dan pesan flash tampil dalam Bahasa Inggris formal
  And Tidak ada satupun teks Bahasa Indonesia mentah yang tersisa
```

### FR-05: Multi-Sheet Excel Export Engine
- **Aktor:** Business Owner / Akuntan
- **Kebutuhan:** Ekspor Excel menghasilkan 2 lembar kerja dengan format profesional.
```gherkin
Scenario: Pengguna mengunduh laporan stok gudang ke Excel
  Given Pengguna berada di halaman detail gudang
  When Pengguna menekan tombol "Ekspor Excel (XLSX)"
  Then File XLSX diunduh dengan Sheet 1 (Ringkasan Eksekutif & KPI Bento) dan Sheet 2 (Buku Pembantu Mutasi & Valuasi Stok)
  And Seluruh kolom nominal bertipe Number dengan format "Rp #,##0"
  And Baris total menggunakan formula dinamis "=SUM(...)"
```

---

## 4. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Keamanan (Security):**
   - Zero Plaintext Credential Exposure.
   - Strict CSRF token pada setiap form submission dan AJAX POST/PUT/DELETE.
   - Rate limiting pada endpoint pencarian geocoding GPS dan Biteship.
2. **Performa (Performance):**
   - Waktu respon halaman `< 200ms` dengan eager loading relasi (`with(['parent', 'children', 'product.outputUnit'])`).
   - Debounce `300ms` pada input pencarian live untuk meminimalkan beban query database.
3. **Aksesibilitas & Ergonomi (Accessibility):**
   - Kontras teks WCAG AA $\ge 4.5:1$ pada light mode dan dark mode.
   - Ukuran font input form mobile minimal `16px` (`text-[16px] sm:text-xs`) untuk mencegah auto-zoom pada Safari iOS.
   - Target sentuh tombol minimal `44x44px` di perangkat layar sentuh.

---

## 5. Spesifikasi Antarmuka Bento Apple HIG v2.0

1. **Struktur Header 3-Baris:**
   - Baris 1: Overline Typographic Kategori (`text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`).
   - Baris 2: H1 Title Besar + Status Badge + Action Button Primer (`bg-[#007AFF] text-white rounded-xl px-4 py-2`).
   - Baris 3: Subtitle Faktual 1 Baris Padat (Maksimal 15 kata).
2. **Modal Sheet XXL 2-Kolom:**
   - Geometri Squircle Continuously Curved (`rounded-[22px]`).
   - Frosted Glass Backdrop (`backdrop-blur-2xl bg-white/98 dark:bg-[#1C1C1E]/98`).
   - Grid 2-Kolom Lapang (`grid grid-cols-1 lg:grid-cols-12 gap-6`).
3. **Underline Tab Bar dengan Deep-Linking:**
   - Tab border bawah halus dengan active indicator `#007AFF`.
   - URL parameter sinkron secara instan via `window.history.replaceState`.
