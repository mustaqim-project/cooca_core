# Rencana Implementasi: Channel Visibility, Proteksi Harga Web, dan Pre-Order B2C

Dokumen rencana implementasi arsitektur hulu-ke-hilir (*database, backend, admin UI, channel query scoping, validasi pesanan, dan storefront checkout*) untuk mengakomodasi kebutuhan multi-saluran produk, proteksi visibilitas harga publik via WhatsApp inquiry, serta sistem Pre-Order granular (Batch Penjual & Jadwal Pelanggan) pada platform COOCA.

> **Status:** Menunggu Konfirmasi / Arahan Pengguna (`PENDING_USER_COMMAND`)  
> **Klasifikasi Risiko:** `Structural Change` & `Business Logic Change`  
> **Target Branch / Workspace:** `c:\laragon\www\cooca_core`  
> **Format Dokumen:** Markdown Terstruktur (Dapat ditambahkan / direvisi oleh pengguna sebelum dieksekusi)

---

## 1. Latar Belakang & Kebutuhan Bisnis

UMKM di Indonesia, khususnya sektor Food & Beverage (Katering harian, kue rumahan, tumpeng mini, artisan pastry) serta manufaktur pesanan khusus, memiliki karakteristik operasional yang sangat dinamis:

1. **Visibilitas Multi-Saluran (Channel Scoping):**
   - Ada produk yang hanya dijual di meja kasir fisik (POS Terminal) dan tidak boleh muncul di website katalog (misalnya: es batu, kantong belanja, air mineral gelas).
   - Ada produk pesanan korporat/grosir (Sales Order B2B) yang memiliki harga dan volume khusus sehingga tidak boleh diakses oleh pelanggan retail umum di web.
   - Ada produk khusus etalase online (Website Storefront) yang tidak perlu dimuat di memori kasir offline.

2. **Proteksi Tampilan Harga di Web (Custom Pricing / WhatsApp Inquiry):**
   - Untuk produk *custom-made* (misalnya: kue ulang tahun bertema, paket catering resepsi, suvenir custom), harga final bergantung pada negosiasi dan rincian permintaan pelanggan.
   - Pemilik bisnis membutuhkan opsi **"Sembunyikan Harga di Web"**. Jika diaktifkan, produk tetap tampil di etalase toko online tetapi harganya tertulis *"Hubungi Kami"* dan tombol langsung mengarahkan pelanggan ke WhatsApp bisnis, **tanpa mengubah atau mengenolkan harga pokok/harga jual (`selling_price`)** asli di database admin maupun kasir POS.

3. **Sistem Pre-Order (PO) Granular per Produk:**
   - **Mode Jadwal Pelanggan (`customer_schedule`):** Pelanggan bebas memilih tanggal pengantaran/pengambilan, dengan syarat memenuhi batas waktu persiapan minimum (*Lead Time*, misal: minimal H-2 atau H-3 sebelum tanggal konsumsi).
   - **Mode Batch Penjual (`merchant_batch`):** Penjual membuka kuota pengiriman pada tanggal tertentu (misalnya: Pengiriman Batch Jumat Berkah atau Batch PO Lebaran) yang dapat ditautkan ke fitur order batch yang sudah ada di COOCA.

---

## 2. Prinsip Arsitektur & Hard Guardrails

Sesuai dengan `cooca-agent-directive` dan pedoman desain sistem COOCA:

1. **Zero-Breaking Change & Backward Compatibility:**
   - Semua kolom baru pada migrasi tabel `products` diberi nilai bawaan (`default`):
     - `show_in_website = true`
     - `show_in_pos = true`
     - `show_in_sales_order = true`
     - `show_price_on_web = true`
     - `is_preorder = false` (Ready Stock)
     - `preorder_mode = 'customer_schedule'`
     - `preorder_lead_days = 1`
   - Produk yang sudah ada di database produksi tidak akan mengalami perubahan perilaku transaksi maupun visibilitas.
2. **Strict Multi-Tenant Isolation:**
   - Setiap query Eloquent wajib di-scope menggunakan `business_id` tenant aktif.
3. **Integritas Nilai Finansial:**
   - Kolom `selling_price` di tabel `products` tetap menyimpan nilai asli untuk keperluan HPP, laporan kasir POS, dan margin laba rugi. Proteksi `show_price_on_web = false` hanya memengaruhi layer presentasi Storefront publik.
4. **Bento Apple HIG UI Compliance:**
   - Tanpa emoji pada kode Blade dan label form.
   - Tipografi angka menggunakan kelas `tabular-nums font-mono`.
   - Menggunakan geometri squircle Apple dan penataan kartu Bento lapang (*Zero-Manual UI*).
   - Anti-pill abuse: badge status Pre-Order dirancang subtle dan proporsional.

---

## 3. Matriks Skema Database (Database Layer)

Berkas migrasi: `database/migrations/2026_09_17_083000_add_channel_visibility_and_preorder_to_products_table.php`

| Nama Kolom | Tipe Data | Nilai Default | Indeks | Fungsi & Perilaku Sistem |
|---|---|---|---|---|
| `show_in_website` | `BOOLEAN` | `true` | Ya | Menentukan apakah produk tampil di katalog etalase website publik. |
| `show_in_pos` | `BOOLEAN` | `true` | Ya | Menentukan apakah produk tampil di terminal kasir kasir offline/online (POS). |
| `show_in_sales_order` | `BOOLEAN` | `true` | Ya | Menentukan apakah produk dapat dipilih pada dokumen Sales Order / Faktur B2B. |
| `show_price_on_web` | `BOOLEAN` | `true` | Tidak | Jika `false`, harga disembunyikan di website etalase dan tombol berubah menjadi WhatsApp inquiry. |
| `is_preorder` | `BOOLEAN` | `false` | Ya | Menandai produk sebagai item Pre-Order (bukan *Ready Stock*). |
| `preorder_mode` | `VARCHAR(30)` | `'customer_schedule'` | Tidak | Pilihan mode: `customer_schedule` (jadwal fleksibel) atau `merchant_batch` (batch toko). |
| `preorder_lead_days` | `UNSIGNED SMALLINT` | `1` | Tidak | Jumlah hari persiapan minimum yang dibutuhkan sebelum pesanan dapat dikirim/diambil. |

---

## 4. Rincian Perubahan Komponen (Hulu ke Hilir)

### A. Model Layer (`app/Models/Product.php`)
- **Konstanta:**
  - `Product::PREORDER_MODE_BATCH = 'merchant_batch'`
  - `Product::PREORDER_MODE_SCHEDULE = 'customer_schedule'`
- **Eloquent Scopes:**
  - `scopeForPos($query)`: menyaring produk aktif dengan `show_in_pos = true`.
  - `scopeForStorefront($query)`: menyaring produk aktif dengan `show_in_website = true`.
  - `scopeForSalesOrder($query)`: menyaring produk aktif dengan `show_in_sales_order = true`.
  - `scopePreorders($query)`: menyaring produk dengan `is_preorder = true`.
- **Helper Methods:**
  - `isPreorder(): bool`
  - `isPriceVisibleOnWeb(): bool`
- **Casting & Fillable:** Menambahkan ketujuh field ke `$fillable` dan `$casts` (`boolean` & `integer`).

### B. Controller & Channel Scoping Layer
1. **`app/Http/Controllers/Web/ProductWebController.php` & `ServiceWebController.php`:**
   - Menambahkan aturan validasi pada method `store()` dan `update()`:
     - `show_in_website` => `['nullable', 'boolean']`
     - `show_in_pos` => `['nullable', 'boolean']`
     - `show_in_sales_order` => `['nullable', 'boolean']`
     - `show_price_on_web` => `['nullable', 'boolean']`
     - `is_preorder` => `['nullable', 'boolean']`
     - `preorder_mode` => `['nullable', 'string', 'in:merchant_batch,customer_schedule']`
     - `preorder_lead_days` => `['nullable', 'integer', 'min:0', 'max:90']`
   - Menyimpan nilai boolean secara deterministik (`$request->boolean(...)`).
2. **`app/Http/Controllers/Web/Pos/PosTerminalWebController.php`:**
   - Memodifikasi query produk POS dari query generik menjadi `->forPos()`.
3. **`app/Http/Controllers/Web/Sales/SalesOrderWebController.php`:**
   - Memodifikasi query dropdown produk Sales Order menjadi `->forSalesOrder()`.
4. **`app/Http/Controllers/Web/PublicBusinessLandingController.php`:**
   - Menyaring katalog website publik menggunakan `->forStorefront()`.
   - Mengirimkan metadata ke payload JSON Alpine.js:
     - `show_price_on_web`: boolean
     - `is_preorder`: boolean
     - `preorder_mode`: string
     - `preorder_lead_days`: integer
   - Jika `show_price_on_web == false`, controller menyembunyikan nominal harga pada etalase web publik (`price = 0`) untuk memicu state "Hubungi Kami" secara aman tanpa mengekspos angka melalui inspeksi jaringan (Network Tab/Payload JSON).

### C. Domain Commerce & Order Validation (`app/Domain/Commerce/Storefront/CommerceOrderService.php`)
- **Validasi Integritas Saluran Web:**
  - Menolak pembuatan order jika produk di keranjang memiliki `show_in_website == false`.
- **Validasi Pre-Order & Lead Time:**
  - Mendeteksi apakah keranjang berisi item `is_preorder == true`.
  - Jika ada item Pre-Order:
    1. Memvalidasi bahwa checkout menggunakan mode terjadwal (`scheduled_date` tidak boleh kosong).
    2. Menghitung `max_lead_days` dari seluruh item PO di keranjang.
    3. Memastikan bahwa tanggal jadwal yang dipilih pelanggan memenuhi:  
       `scheduled_date >= Carbon::today()->addDays($maxLeadDays)`.
    4. Melempar `ValidationException` yang jelas dan ramah jika pelanggan memilih tanggal yang melanggar batas lead time.

### D. Front-End Admin: Manajemen Produk (`resources/views/app/products/index.blade.php`)
- Menambahkan **Sub-Card Bento Apple HIG** di modal Tambah Produk dan Edit Produk:
  1. **Blok Saluran Penjualan:**
     - Toggle / Checkbox bergaya iOS: *Kasir POS*, *Faktur / Sales Order*, *Etalase Website*.
  2. **Blok Visibilitas Harga Web:**
     - Toggle: *Tampilkan Harga di Web*.
     - Keterangan edukatif: *"Bila dinonaktifkan, harga akan disembunyikan dan dialihkan ke tombol konsultasi WhatsApp langsung."*
  3. **Blok Konfigurasi Pre-Order (PO):**
     - Toggle: *Produk Sistem Pre-Order (PO)*.
     - Kontrol Reaktif (Alpine.js):
       - Pilihan Mode: *Jadwal Fleksibel Pelanggan* vs *Sesuai Batch Toko*.
       - Input Jumlah Hari Persiapan (*Lead Time*) dengan label yang ergonomis.

### E. Front-End Etalase Publik & Checkout (`resources/views/public/business_landing.blade.php`)
- **Kartu Produk (Katalog & Pencarian):**
  - Menampilkan badge squircle elegan *"Pre-Order"* atau *"PO H-X"* untuk produk pre-order.
  - Untuk produk dengan `show_price_on_web == false`:
    - Mengganti teks harga dengan label *"Hubungi Kami"*.
    - Tombol aksi utama berubah menjadi tautan WhatsApp dengan pesan otomatis terformat rapi: *"Halo, saya ingin menanyakan harga dan detail pemesanan untuk [Nama Produk]"*.
- **Modal Checkout & Keranjang:**
  - Jika keranjang mendeteksi item pre-order, opsi *Pengiriman Hari Ini (Same Day)* dinonaktifkan secara otomatis.
  - Opsi *Jadwal Pengiriman / Pre-Order* otomatis tercentang dan tanggal minimum dibatasi oleh `min_date = today + max_lead_days`.

---

## 5. Rencana Pengujian Nyata (Verification & QA Plan)

### Automated Test Suites
1. **Linting Sintaks (PHP Syntax Check):**
   ```powershell
   php -l app/Models/Product.php
   php -l app/Http/Controllers/Web/ProductWebController.php
   php -l app/Http/Controllers/Web/ServiceWebController.php
   php -l app/Http/Controllers/Web/Pos/PosTerminalWebController.php
   php -l app/Http/Controllers/Web/Sales/SalesOrderWebController.php
   php -l app/Http/Controllers/Web/PublicBusinessLandingController.php
   php -l app/Domain/Commerce/Storefront/CommerceOrderService.php
   ```
2. **Pengecekan Status Migrasi:**
   ```powershell
   php artisan migrate:status
   ```
3. **Pembuatan & Eksekusi Feature Test Baru:**
   - Berkas pengujian: `tests/Feature/ProductChannelVisibilityAndPreorderTest.php`
   - Skenario yang diuji:
### E. Fitur Baru & Perbaikan: Quick Toggle Data Table & Form Persistence
1. **Perbaikan Persistensi Checkbox Edit (`ProductWebController::update`):**
   - Mengganti pembacaan checkbox boolean dari `$request->has(...) ? ... : $product->...` menjadi `$request->boolean(...)` agar saat checkbox di-*uncheck* (yang tidak dikirimkan oleh browser), nilai tersimpan di database menjadi `false` (0).
   - Menambahkan elemen fallback `<input type="hidden" name="[field]" value="0">` sebelum masing-masing checkbox pada formulir modal tambah dan edit produk.
2. **Endpoint AJAX Cepat (`products.toggle-setting`):**
   - Route: `POST /products/{product}/toggle-setting`
   - Controller: `ProductWebController::toggleSetting(Request $request, Product $product)`
   - Guard multi-tenant: `business_id === $product->business_id`, middleware `require.permission:products.edit`.
   - Validasi: `field` (`show_in_website`, `show_in_pos`, `show_in_sales_order`, `show_price_on_web`, `is_preorder`, `is_active`), `value` (`nullable|boolean`).
3. **Data Table Bento Apple HIG (`resources/views/app/products/index.blade.php`):**
   - Menambahkan kolom `Saluran & Status` pada desktop table (`hidden sm:block`) dengan tombol mikro squircle continuo:
     - `Web` (Biru Apple)
     - `POS` (Hijau Apple)
     - `SO` (Ungu Apple)
     - `Harga` (Teal Apple)
     - `PO` (Oranye Apple)
     - `Aktif / Nonaktif` (Hijau/Merah)
   - Menambahkan baris quick toggle yang sama pada setiap kartu produk di mobile list view (`sm:hidden`).
   - Micro-interaction Alpine.js: Optimistic UI toggle, SweetAlert2 toast notification, dan auto-revert saat network error.

---

## 5. Rencana Pengujian (Testing Strategy)

1. **Feature Test Persistensi Checkbox Unchecked:**
   - Kirim `PUT /products/{product}` tanpa parameter `show_in_website` (seperti payload browser saat uncheck).
   - Verifikasi kolom `show_in_website` di database bernilai `false` (0).
2. **Feature Test Quick Toggle AJAX Endpoint:**
   - Kirim `POST /products/{product}/toggle-setting` untuk field `show_in_website`, `show_in_pos`, `is_preorder`, dll.
   - Verifikasi response JSON 200 `status: success` dan nilai di database terupdate secara presisi.
   - Verifikasi proteksi IDOR: request dari tenant lain menghasilkan HTTP 403.
3. **Automated Unit & Feature Test Eksisting:**
   ```powershell
   php artisan test tests/Feature/ProductChannelVisibilityAndPreorderTest.php
   php artisan test tests/Feature/CommerceStorefrontCheckoutTest.php
   ```

---

## 6. Ruang Catatan & Penyesuaian Pengguna (User Additions)

> Bagian ini disiapkan khusus agar Anda dapat menambahkan catatan, aturan bisnis khusus, atau pertanyaan tambahan sebelum eksekusi dimulai. Silakan edit atau beri arahan pada area berikut:

```markdown
[CATATAN PENGGUNA / USER NOTES]:
1. Checkbox uncheck issue dilaporkan dan dianalisis tuntas (browser HTML omission fix via $request->boolean).
2. Permintaan Quick Toggle pada Data Table index produk ditambahkan ke rencana implementasi.
```

---

## 7. Prosedur Konfirmasi Eksekusi (Next Step)

Sesuai instruksi: **"jangan eksekusi implementasi sebelum ada perintah"**, langkah eksekusi kode saat ini **DITANGGUHKAN** sepenuhnya.

Bila rencana ini sudah sesuai:
- Berikan instruksi seperti: **"Lanjutkan eksekusi sesuai plan"**
- AI Agent akan mengeksekusi sisa langkah secara presisi, menjalankan automated testing hingga 100% lulus, dan menyajikan laporan walkthrough akhir.
