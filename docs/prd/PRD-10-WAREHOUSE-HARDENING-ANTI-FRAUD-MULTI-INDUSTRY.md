# PRD-10: Hardening Keamanan, Proteksi Fraud Internal Penyesuaian Stok, & Antarmuka Sadar Konteks 20 Industri pada Modul Cabang & Gudang

> **Status:** IMPLEMENTED & VERIFIED 100%  
> **Versi:** 1.0  
> **Penanggung Jawab:** Security Engineer, Senior Laravel Architect, Senior UI/UX Engineer, Accounting & ERP Specialist  
> **Modul Terkait:** `resources/views/app/warehouse/`, `WarehouseWebController`, `InventoryWebController`, `StockService`, `AutoJournalService`, `Location`  
> **Dokumen Terkait:** [`docs/system/workflows/warehouse-logistics-and-multi-branch-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/warehouse-logistics-and-multi-branch-flow.md), [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

Modul **Cabang & Gudang Logistik (Warehouse Management Hub)** pada `resources/views/app/warehouse` memegang peran vital dalam ekosistem COOCA Enterprise sebagai pengatur titik fisik bisnis (Cabang, Outlet, Dapur Pusat, Pabrik, dan Gudang Logistik), penentu titik asal penjemputan ekspedisi toko online storefront (Biteship), penentu batas radius absensi presensi geofence staf, serta pintu masuk pengelolaan kartu stok fisik dan penyesuaian inventori (*Stock Adjustment*).

Meskipun modul ini telah mengadopsi antarmuka Bento Apple HIG modern dan struktur hierarki parent-child (*self-referencing parent_id*), audit komprehensif hulu-ke-hilir mengungkap **7 kesenjangan kritis (*critical gaps*)** yang mengancam keamanan data, membuka celah fraud penggelapan inventori, merusak keseimbangan buku besar akuntansi, dan membingungkan pengguna UMKM lintas industri:

1. **Celah IDOR Lintas Tenant pada Penyesuaian Stok:** Endpoint `inventory.stocks.adjust` menerima `location_id` dan `product_id` tanpa validasi kepemilikan bisnis aktif (`business_id`).
2. **Fraud Penggelapan Barang via Penyesuaian Stok Minus (Phantom Write-Off):** Staf operasional dapat mengurangi saldo stok fisik bernilai jutaan rupiah menjadi 0 secara instan tanpa otorisasi **Supervisor PIN** dan tanpa Berita Acara baku.
3. **Ketiadaan Auto-Journal untuk Selisih Persediaan (Inventory Shrinkage):** Penyesuaian stok hanya memotong kuantitas fisik di tabel inventori tanpa menerbitkan jurnal akuntansi ganda (*Beban Selisih Stok* vs *Persediaan*) ke buku besar, mengakibatkan laporan Neraca dan Laba Rugi tidak mencerminkan nilai fisik riil.
4. **Hard Delete Destruktif yang Merusak Jejak Audit:** Penghapusan lokasi yang memiliki riwayat transaksi masa lalu (`stock_movements`, `goods_receipts`, `pos_orders`) dijalankan dengan hard delete (`$location->delete()`), memicu kegagalan SQL foreign key constraint atau merusak integritas data historis.
5. **Flaw Entitlement Gating:** Route `POST /warehouse` dikawal secara statis oleh middleware `entitlement:warehouse`, padahal form yang sama digunakan untuk membuat Cabang (`type = 'outlet'`). Tenant dengan kuota outlet tersedia akan terblokir jika kuota gudang habis, atau sebaliknya membypass kuota outlet jika kuota gudang masih ada.
6. **Kebocoran Antarmuka Lintas Industri (UI Clutter):** Pilihan *"Dapur Pusat (Central Kitchen)"* dan tombol *"Pengiriman Storefront"* muncul secara seragam untuk seluruh industri (termasuk Bengkel Mobil, Apotek, Konveksi, Kontraktor, dan Salon).
7. **Kerentanan Double-Submit & Ketiadaan Audit Trail:** Tombol submit modal form tidak memiliki loading state `x-bind:disabled="loading"`, dan mutasi konfigurasi gudang belum mencatat riwayat immutable ke tabel `audit_logs`.

Dokumen PRD ini merinci kebutuhan fungsional, arsitektur mitigasi defensif, kriteria penerimaan berbasis Gherkin, dan penataan antarmuka sadar konteks (*Context-Aware UI*) untuk 20 sektor industri bisnis di COOCA.

---

## 2. Analisis Kesenjangan Sistem Existing (Gap Analysis)

```text
┌───────────────────────────────────────┬──────────────────────────────────────────┐
│ KONDISI SEKARANG (CURRENT STATE)       │ KONDISI TARGET (TARGET ENTERPRISE STATE)  │
├───────────────────────────────────────┼──────────────────────────────────────────┤
│ 1. Endpoint adjust hanya validasi     │ 1. Wajib Rule::exists() scoped to        │
│    string generic (Rentan IDOR).      │    business_id aktif.                    │
│ 2. Staf bebas memotong stok tanpa PIN │ 2. Potong stok minus > threshold wajib   │
│    Supervisor dan tanpa alasan baku.  │    PIN Supervisor + Berita Acara baku.   │
│ 3. Penyesuaian stok tidak membuat     │ 3. Terintegrasi AutoJournalService:      │
│    jurnal akuntansi (Neraca timpang). │    Debit Beban Selisih, Kredit Persediaan│
│ 4. Hapus lokasi pakai hard-delete     │ 4. Soft-delete / Deactivation guard jika │
│    jika saldo stok = 0.               │    ada jejak transaksi historis.         │
│ 5. Middleware entitlement:warehouse   │ 5. Dynamic entitlement check sesuai      │
│    statis di route.                   │    parameter `type` (outlet vs warehouse)│
│ 6. Opsi Dapur Pusat bocor ke Bengkel, │ 6. Blade conditional gating sesuai       │
│    Apotek, Konveksi, Kontraktor.      │    template_code 20 sektor industri.     │
│ 7. Klik ganda form memicu duplikasi   │ 7. Alpine.js loading disable state +     │
│    dan zero audit trail.              │    pencatatan penuh ke audit_logs.       │
└───────────────────────────────────────┴──────────────────────────────────────────┘
```

---

## 3. Ruang Lingkup Proyek (Scope & Non-Scope)

### Dalam Cakupan (In-Scope):
1. **Hardening Keamanan & IDOR:** Pengetatan validasi input pada `InventoryWebController@quickAdjust` dan `WarehouseWebController@store/@update`.
2. **Proteksi Fraud Internal Stok:** Penegakan otorisasi Supervisor PIN (Bcrypt-hashed), penguncian kode Berita Acara baku (`reason_code`), dan pencatatan audit log terstruktur.
3. **Integritas Finansial & Auto-Journal:** Integrasi `AutoJournalService` untuk mutasi selisih stok (Beban Selisih Stok vs Persediaan Barang Dagang).
4. **Perlindungan Integritas Lokasi:** Implementasi pengalihan hard-delete menjadi penonaktifan operasional (`is_active = false`) jika lokasi memiliki transaksi historis.
5. **Dinamisasi Entitlement:** Pemisahan validasi kuota outlet (`entitlement:outlet`) dan kuota gudang (`entitlement:warehouse`) pada backend controller.
6. **Penataan UI Sadar Konteks 20 Industri:** Blade conditional gating untuk dropdown tipe lokasi, tombol navigasi header, dan kamus istilah adaptif per sektor industri.
7. **Ergonomi & Aksesibilitas UI:** Proteksi double-submit, format pemisah ribuan otomatis, dan rate-limiting pada endpoint geocoding.

### Di Luar Cakupan (Non-Scope):
1. Mengubah struktur perhitungan biaya HPP metode *Moving Average* yang sudah berjalan di `StockService`.
2. Mengubah skema integrasi kurir API Biteship yang sudah ada.
3. Menghapus data transaksi riil yang sudah berstatus selesai di database.

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### FR-01: Validasi Scoping Tenant & Proteksi IDOR Mutlak
* **Deskripsi:** Backend wajib memvalidasi bahwa `location_id` dan `product_id` yang dikirim pada saat penyesuaian stok atau konfigurasi cabang secara eksplisit milik tenant aktif (`business_id`).
* **Implementasi:** Penerapan `Rule::exists('locations', 'id')->where('business_id', $businessId)` dan `Rule::exists('products', 'id')->where('business_id', $businessId)`.

### FR-02: Otorisasi Supervisor PIN pada Penyesuaian Stok Minus
* **Deskripsi:** Setiap penyesuaian stok yang mengurangi kuantitas fisik melebihi ambang batas toleransi wajib memverifikasi PIN Supervisor 6-digit.
* **Aturan Ambang Batas (Threshold):**
  - $\Delta \text{Valuasi} > \text{Rp } 100.000$, ATAU
  - $|\Delta \text{Kuantitas}| > 10\text{ unit}$.
* **Verifikasi:** PIN diverifikasi terhadap `businesses.supervisor_pin_hash` menggunakan `Hash::check()`. Jika tidak cocok atau kosong, sistem membatalkan transaksi dan mengembalikan error validasi.

### FR-03: Standardisasi Kode Berita Acara (Reason Codes)
* **Deskripsi:** Form penyesuaian stok wajib menyediakan pilihan alasan baku terstruktur:
  1. `damaged`: Barang Rusak / Cacat Fisik / Basi
  2. `expired`: Melewati Tanggal Kadaluarsa
  3. `opname_variance`: Selisih Hitung Rutin Stock Opname
  4. `theft_loss`: Kehilangan / Dugaan Pencurian
  5. `initial_balance`: Input Saldo Awal Gudang
  6. `other`: Lainnya (Wajib melampirkan teks penjelasan minimal 10 karakter)

### FR-04: Pencatatan Jurnal Akuntansi Otomatis Selisih Persediaan
* **Deskripsi:** Saat penyesuaian stok berhasil disimpan, sistem secara otomatis menerbitkan dokumen `JournalEntry` berpasangan melalui `AutoJournalService`:
  - **Kasus Pengurangan Stok (Defisit / Shrinkage):**
    - Debit: `6-6004` (Beban Kerugian Selisih Persediaan) senilai $|\Delta \text{Qty}| \times \text{HPP}$
    - Kredit: `1-1004` (Persediaan Barang Dagang) senilai $|\Delta \text{Qty}| \times \text{HPP}$
  - **Kasus Penambahan Stok (Surplus / Penemuan):**
    - Debit: `1-1004` (Persediaan Barang Dagang) senilai $\Delta \text{Qty} \times \text{HPP}$
    - Kredit: `7-7004` (Pendapatan Lain-lain / Penyesuaian Persediaan) senilai $\Delta \text{Qty} \times \text{HPP}$

### FR-05: Non-Destructive Location Archival Guard
* **Deskripsi:** Aksi penghapusan lokasi (`DELETE /warehouse/{location}`) wajib memeriksa keterkaitan data historis:
  - Jika terdapat catatan di `stock_movements`, `goods_receipts`, atau `pos_orders`, lokasi **DILARANG DIHAPUS KERAS (HARD DELETE)**.
  - Sistem secara otomatis mengalihkan aksi menjadi penonaktifan status operasional (`is_active = false`) dan memberikan pesan penenang jiwa:  
    *"Lokasi memiliki riwayat transaksi masa lalu. Lokasi telah dinonaktifkan dengan aman untuk melindungi data pembukuan dan audit trail."*

### FR-06: Dynamic Entitlement Gating untuk Outlet vs Warehouse
* **Deskripsi:** Endpoint `POST /warehouse` wajib memvalidasi kuota fitur secara dinamis berdasarkan parameter `type`:
  - Jika `type === 'outlet'`, periksa kuota cabang via `EntitlementService::canCreateLocation($business, 'outlet')`.
  - Jika `type === 'warehouse'`, periksa kuota gudang via `EntitlementService::canCreateLocation($business, 'warehouse')`.

### FR-07: Antarmuka Sadar Konteks (Context-Aware UI) 20 Sektor Industri
* **Deskripsi:** Tampilan antarmuka pada `index.blade.php` dan `show.blade.php` wajib beradaptasi secara otomatis berdasarkan `$business->template_code`:
  - **Dropdown Tipe Lokasi:**
    - Klaster F&B: Menampilkan pilihan *Cabang Outlet*, *Gudang Penyimpanan*, dan *Dapur Pusat (Central Kitchen)*.
    - Klaster Manufaktur: Menampilkan pilihan *Cabang Kantor*, *Gudang Bahan Mentah/Barang Jadi*, dan *Pabrik / Workshop Produksi*.
    - Klaster Kontraktor: Menampilkan pilihan *Kantor Cabang*, *Gudang Material*, dan *Basecamp / Workshop Proyek*.
    - Klaster Ritel & Jasa: Menampilkan pilihan *Cabang / Gerai Toko* dan *Gudang Penyimpanan* (Opsi Dapur Pusat disembunyikan total).
  - **Tombol Navigasi Header:**
    - Tombol *[Pengiriman Storefront]* hanya muncul jika modul `merchant_shipping` atau `storefront_checkout` aktif.
    - Tombol *[Katalog Bahan]* hanya muncul jika modul `recipe_bom` aktif.

---

## 5. Kriteria Penerimaan (Acceptance Criteria - Gherkin Format)

```gherkin
Feature: Keamanan & Scoping Multi-Tenant Penyesuaian Stok

  Scenario: Percobaan IDOR lintas tenant ditolak
    Given staf login pada tenant "Kopi Kenangan"
    When staf mengirim request penyesuaian stok dengan location_id milik tenant "Bengkel Maju"
    Then sistem menolak request dengan status HTTP 422
    And pesan kesalahan menyatakan lokasi tidak valid untuk bisnis aktif

  Scenario: Penyesuaian stok minus bernilai besar diblokir tanpa PIN Supervisor
    Given stok barang saat ini bernilai Rp 500.000
    When staf menginput kuantitas baru yang menyebabkan kerugian Rp 300.000 tanpa mengisi supervisor_pin
    Then sistem membatalkan transaksi dengan pesan "Penyesuaian stok melebihi toleransi. PIN Supervisor 6-digit wajib diisi"

  Scenario: Penyesuaian stok berhasil menerbitkan jurnal akuntansi otomatis
    Given staf memasukkan kuantitas baru dengan alasan "expired" dan PIN Supervisor benar
    When formulir penyesuaian stok disubmit
    Then saldo fisik di inventory_stocks terupdate
    And tercatat mutasi di stock_movements
    And terbit JournalEntry berpasangan mendebit Beban Kerugian Selisih Persediaan dan mengkredit Persediaan Barang Dagang
    And tercatat entri baru di tabel audit_logs
```

```gherkin
Feature: Perlindungan Integritas Riwayat Transaksi Lokasi

  Scenario: Penghapusan lokasi dengan riwayat transaksi dialihkan menjadi penonaktifan
    Given lokasi "Gudang Rawamangun" memiliki 15 riwayat stock_movements dan saldo stok = 0
    When pemilik usaha mengonfirmasi penghapusan lokasi
    Then sistem TIDAK menghapus baris tabel locations
    And mengubah kolom is_active menjadi false
    And menampilkan notifikasi sukses penonaktifan aman
```

```gherkin
Feature: Antarmuka Sadar Konteks 20 Sektor Industri

  Scenario: Tampilan form gudang pada Bengkel Mobil
    Given bisnis terdaftar dengan template_code "service_workshop"
    When pengguna membuka modal tambah atau ubah lokasi
    Then pilihan "Dapur Pusat (Central Kitchen)" TIDAK MUNCUL di dropdown
    And tombol "Pengiriman Storefront" TIDAK MUNCUL di header halaman
```

---

## 6. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Keamanan (Security):**
   - Zero Plaintext Credential Exposure: PIN Supervisor tidak boleh terpapar di DOM HTML, JavaScript variable, atau response JSON.
   - Rate limiting ketat `throttle:60,1` pada route geocoding untuk mencegah abuse kuota API pihak ketiga.
2. **Kinerja & Responsivitas (Performance):**
   - Query pencarian stok dan penyesuaian stok wajib memiliki waktu respon sub-150ms.
   - Eksekusi penyesuaian stok dibungkus dalam `DB::transaction()` atomik.
3. **Ergonomi & Aksesibilitas (Apple HIG & Usability):**
   - Tombol submit formulir dilindungi indikator visual `x-bind:disabled="loading"` dan spinner pemroses.
   - Input harga HPP dan kuantitas diformat dengan tanda pemisah ribuan otomatis (`tabular-nums`).
   - Ukuran font input minimal 16px di mobile anti-auto-zoom iOS Safari, touch target 48px–52px.

---

## 7. Kamus Terminologi Adaptif 20 Sektor Industri

| Klaster Industri | Sektor Usaha | Istilah Cabang / Outlet | Istilah Gudang / Titik Simpan | Istilah Workshop / Pusat |
|---|---|---|---|---|
| **1. Kuliner & F&B** | Restoran, Cafe, Bakery, Katering | Cabang / Outlet Resto | Gudang Bahan / Chiller | Dapur Pusat (Central Kitchen) |
| **2. Manufaktur** | Garment, Logam, Mebel, Percetakan | Kantor Pemasaran / Showroom | Gudang Bahan Baku / Barang Jadi | Pabrik / Workshop Produksi |
| **3. Ritel & Apotek** | Minimarket, Apotek / Toko Obat | Toko / Gerai Apotek | Gudang Obat / Etalase Depan | Distribution Center (DC) |
| **4. Jasa Operasional** | Bengkel Mobil/Motor, Salon, Laundry | Bengkel / Pit Servis | Gudang Suku Cadang & Sparepart | Workshop Utama |
| **5. Jasa Proyek** | Kontraktor, Event Organizer, Agency | Kantor Operasional | Gudang Material Konstruksi | Basecamp / Site Proyek |
| **6. Distribusi & Agro** | Distributor FMCG, Pertanian | Kantor Cabang Regional | Gudang Transit / Cold Storage | Distribution Center (Pusat) |

---

## 8. Persetujuan & Tanda Tangan Dokumen

| Peran | Nama / Agen | Status | Tanggal |
|---|---|:---:|:---:|
| **Security Architect** | AI Security Auditor | `APPROVED` | 2026-09-27 |
| **Full-Stack Engineer** | AI Lead Engineer | `APPROVED` | 2026-09-27 |
| **Business Owner / User** | *Menunggu Konfirmasi User* | `PENDING` | - |
