# Rencana Aksi & Implementasi: Hardening Keamanan, Proteksi Fraud Penyesuaian Stok & UI Sadar Konteks 20 Industri pada Modul Cabang & Gudang

**Referensi Dokumen:** [`docs/prd/PRD-10-WAREHOUSE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-10-WAREHOUSE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md)  
**ID Dokumen:** `PLAN-10-WAREHOUSE-HARDENING-ANTI-FRAUD`  
**Penanggung Jawab:** Security Engineer, Senior Laravel Engineer, Senior UI/UX Engineer, QA Engineer  
**Status:** COMPLETED & VERIFIED 100%  

---

## 1. Ringkasan Eksekutif & Sasaran Teknis

Rencana implementasi ini dirancang untuk mengeksekusi perbaikan menyeluruh terhadap modul **Cabang & Gudang Logistik (`resources/views/app/warehouse`)** dan controller terkait (`WarehouseWebController`, `InventoryWebController`, `StockService`, `AutoJournalService`). Perbaikan ini menutup celah IDOR lintas tenant, mencegah modus fraud penggelapan inventori berkedok penyesuaian stok, menyinkronkan selisih fisik ke buku besar akuntansi melalui auto-journal, mengamankan jejak audit transaksi lokasi dari hard-delete destruktif, dan menyesuaikan antarmuka secara sadar konteks (*Context-Aware UI*) untuk 20 sektor industri di COOCA.

### Sasaran Utama:
1. **Zero-IDOR pada Penyesuaian Stok:** Mengunci `location_id` dan `product_id` ke tenant aktif via `Rule::exists()->where('business_id', $business->id)`.
2. **Anti-Fraud Penyesuaian Stok Minus (Phantom Write-Off Guard):** Menegakkan otorisasi **Supervisor PIN** jika nilai selisih $> \text{Rp } 100.000$ atau $> 10\text{ unit}$, serta mewajibkan pemilihan kode alasan baku (*Reason Code*).
3. **Integritas Finansial & Keseimbangan Buku Besar:** Mengintegrasikan `AutoJournalService` untuk mencatat jurnal berpasangan otomatis (*Beban Kerugian Selisih Persediaan* vs *Persediaan Barang Dagang*) saat penyesuaian stok fisik terjadi.
4. **Perlindungan Riwayat Transaksi Lokasi (Non-Destructive Archival Guard):** Mengalihkan aksi hapus lokasi menjadi penonaktifan operasional (`is_active = false`) jika lokasi memiliki transaksi historis masa lalu (`stock_movements`, `goods_receipts`, `pos_orders`).
5. **Dinamisasi Entitlement Gating:** Memisahkan validasi kuota outlet (`entitlement:outlet`) dan kuota gudang (`entitlement:warehouse`) pada backend controller.
6. **Eliminasi Kebocoran UI & Adaptasi 20 Sektor Industri:** Menyembunyikan opsi *"Dapur Pusat"* pada industri non-F&B dan menyembunyikan tombol *"Pengiriman Storefront"* / *"Katalog Bahan"* jika modul terkait dinonaktifkan.
7. **Ergonomi & Pencegahan Human Error:** Menerapkan indikator loading disable `x-bind:disabled="loading"` untuk mencegah *double-submit*, format angka ribuan otomatis, dan rate-limiting `throttle:60,1` pada route geocoding.

---

## 2. Peta Fase Implementasi (5 Tahapan)

```text
┌────────────────────────────────────────────────────────────────────────┐
│ [COMPLETED] FASE 1: Hardening Keamanan, Scoping Tenant IDOR & Rate Limiting │
├────────────────────────────────────────────────────────────────────────┤
│ [COMPLETED] FASE 2: Proteksi Fraud Stok (Supervisor PIN, Berita Acara) │
├────────────────────────────────────────────────────────────────────────┤
│ [COMPLETED] FASE 3: Proteksi Integritas Data Lokasi (Deactivation Guard)│
├────────────────────────────────────────────────────────────────────────┤
│ [COMPLETED] FASE 4: Penataan Antarmuka Sadar Konteks (Context-Aware UI)│
├────────────────────────────────────────────────────────────────────────┤
│ [COMPLETED] FASE 5: Pengujian Otomatis Lolos 100%, Verifikasi & Docs   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Teknis Eksekusi Per Fase

---

### FASE 1: Hardening Keamanan, Scoping Tenant IDOR & Rate Limiting

**Tujuan:** Menutup celah eksploitasi parameter lintas tenant dan mencegah abuse API geocoding.

#### Langkah 1.1: Scoping Validasi Tenant pada Penyesuaian Stok
* **Berkas:** [`app/Http/Controllers/Web/Inventory/InventoryWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Inventory/InventoryWebController.php)
* **Tindakan:** Modifikasi method `quickAdjust()`:
  - Validasi bahwa `location_id` harus ada di tabel `locations` dengan `business_id = $business->id`.
  - Validasi bahwa `product_id` harus ada di tabel `products` dengan `business_id = $business->id`.
  - Validasi `new_quantity` numeric dan min:0.
  - Validasi `reason_code` wajib in: `damaged,expired,opname_variance,theft_loss,initial_balance,other`.

#### Langkah 1.2: Rate Limiting Route Geocoding
* **Berkas:** [`routes/auth.php`](file:///c:/laragon/www/cooca_core/routes/auth.php)
* **Tindakan:** Tambahkan middleware `throttle:60,1` pada endpoint pencarian area dan reverse geocoding:
  ```php
  Route::get('/geo/search-areas', [\App\Http\Controllers\Web\Common\GeoLocationController::class, 'searchAreas'])
      ->middleware('throttle:60,1')
      ->name('geo.search-areas');
  Route::get('/geo/reverse-geocode', [\App\Http\Controllers\Web\Common\GeoLocationController::class, 'reverseGeocode'])
      ->middleware('throttle:60,1')
      ->name('geo.reverse-geocode');
  ```

#### Langkah 1.3: Dinamisasi Entitlement pada Pembuatan Lokasi
* **Berkas:** [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php) & [`app/Http/Controllers/Web/Warehouse/WarehouseWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Warehouse/WarehouseWebController.php)
* **Tindakan:** 
  - Lepaskan middleware statis `entitlement:warehouse` dari route `POST /warehouse` di `routes/owner.php`.
  - Pindahkan validasi entitlement ke dalam `WarehouseWebController@store` menggunakan `EntitlementService`:
    ```php
    $entitlementService = new \App\Domain\Billing\EntitlementService();
    $entitlementType = in_array($validated['type'], ['outlet', 'store', 'central_kitchen']) ? 'outlet' : 'warehouse';
    if (!$entitlementService->canCreateLocation($business, $entitlementType)) {
        return back()->with('error', "Batas kuota {$entitlementType} untuk paket langganan Anda telah tercapai. Silakan upgrade paket untuk menambah lokasi baru.");
    }
    ```

---

### FASE 2: Proteksi Fraud Stok (Supervisor PIN, Berita Acara & Auto-Journal)

**Tujuan:** Menghilangkan celah *phantom write-off* dan menjaga keseimbangan buku besar akuntansi.

#### Langkah 2.1: Verifikasi Supervisor PIN pada Pengurangan Stok Bernilai Besar
* **Berkas:** [`app/Http/Controllers/Web/Inventory/InventoryWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Inventory/InventoryWebController.php)
* **Tindakan:**
  - Hitung nilai selisih nominal: `$diffValue = abs($diff * $unitCost);` dan kuantitas `$diffQty = abs($diff);`.
  - Jika `$diff < 0` (stok berkurang) dan (`$diffValue > 100000` atau `$diffQty > 10`):
    - Wajib memeriksa `supervisor_pin`.
    - Lakukan verifikasi hash Bcrypt:
      ```php
      if (! $request->filled('supervisor_pin') || ! \Illuminate\Support\Facades\Hash::check($request->input('supervisor_pin'), $business->supervisor_pin_hash)) {
          throw \Illuminate\Validation\ValidationException::withMessages([
              'supervisor_pin' => 'Penyesuaian pengurangan stok melebihi batas toleransi. PIN Supervisor 6-digit wajib diisi dengan benar.',
          ]);
      }
      ```

#### Langkah 2.2: Standardisasi Kode Berita Acara & Catatan Wajib
* **Berkas:** [`resources/views/app/warehouse/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/show.blade.php) & [`InventoryWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Inventory/InventoryWebController.php)
* **Tindakan:**
  - Tambahkan dropdown Berita Acara pada modal `showAdjustModal`:
    - `damaged`: Barang Rusak / Cacat Fisik / Basi
    - `expired`: Melewati Tanggal Kadaluarsa
    - `opname_variance`: Selisih Hitung Rutin Stock Opname
    - `theft_loss`: Kehilangan / Dugaan Pencurian
    - `initial_balance`: Saldo Awal Gudang
    - `other`: Lainnya (Wajib menulis alasan di kolom catatan)
  - Tambahkan kolom PIN Supervisor (hanya tampil jika selisih kuantitas negatif $> 10$ atau nilai $> \text{Rp } 100.000$).

#### Langkah 2.3: Integrasi Auto-Journal Selisih Persediaan
* **Berkas:** [`app/Http/Controllers/Web/Inventory/InventoryWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Inventory/InventoryWebController.php)
* **Tindakan:**
  - Panggil `AutoJournalService` atau buat entri jurnal akuntansi berpasangan:
    - Jika `$diff < 0` (Stok Hilang/Rusak/Susut):
      - **Debit:** Akun Beban Kerugian Selisih Persediaan (`6-6004`) senilai `$diffValue`.
      - **Kredit:** Akun Persediaan Barang Dagang (`1-1004`) senilai `$diffValue`.
    - Jika `$diff > 0` (Stok Ditemukan/Surplus):
      - **Debit:** Akun Persediaan Barang Dagang (`1-1004`) senilai `$diffValue`.
      - **Kredit:** Akun Pendapatan Selisih Stok (`7-7004`) senilai `$diffValue`.

---

### FASE 3: Proteksi Integritas Data Lokasi (Deactivation Guard & Audit)

**Tujuan:** Mencegah kerusakan relasi database akibat hard-delete dan memastikan jejak audit terekam permanen.

#### Langkah 3.1: Pengalihan Hard-Delete Menjadi Penonaktifan Aman
* **Berkas:** [`app/Http/Controllers/Web/Warehouse/WarehouseWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Warehouse/WarehouseWebController.php)
* **Tindakan:**
  - Pada method `destroy()`:
    ```php
    // Cek apakah lokasi memiliki riwayat transaksi masa lalu
    $hasHistory = StockMovement::where('location_id', $location->id)->exists()
        || GoodsReceipt::where('location_id', $location->id)->exists()
        || PosOrder::where('location_id', $location->id)->exists()
        || \App\Models\Attendance::where('location_id', $location->id)->exists();

    if ($hasHistory) {
        $location->update(['is_active' => false]);
        return redirect()->route('warehouse.index')
            ->with('success', "Lokasi \"{$location->name}\" memiliki riwayat transaksi masa lalu sehingga telah dinonaktifkan dengan aman untuk melindungi data pembukuan dan audit.");
    }

    $name = $location->name;
    $location->delete();
    return redirect()->route('warehouse.index')->with('success', "Lokasi \"{$name}\" berhasil dihapus.");
    ```

#### Langkah 3.2: Pencatatan Log Audit Immutable
* **Berkas:** [`WarehouseWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Warehouse/WarehouseWebController.php) & [`InventoryWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Inventory/InventoryWebController.php)
* **Tindakan:** Catat aksi `warehouse.created`, `warehouse.updated`, `warehouse.deactivated`, `warehouse.deleted`, dan `stock.adjusted` ke tabel `audit_logs` lengkap dengan `user_id`, `business_id`, IP address, user agent, dan snapshot data.

---

### FASE 4: Penataan Antarmuka Sadar Konteks (Context-Aware UI) 20 Industri

**Tujuan:** Mengeliminasi kekacauan visual (*clutter*), menyesuaikan opsi formulir, dan mengaktifkan terminologi dinamis.

#### Langkah 4.1: Dinamisasi Dropdown Tipe Lokasi
* **Berkas:** [`resources/views/app/warehouse/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/index.blade.php) & [`show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/show.blade.php)
* **Tindakan:**
  - Ganti opsi statis `central_kitchen` dengan conditional gating:
    ```blade
    <option value="outlet">Cabang / Outlet</option>
    <option value="warehouse">Gudang Penyimpanan</option>
    @if(str_starts_with($business->template_code ?? '', 'fnb_'))
        <option value="central_kitchen">Dapur Pusat (Central Kitchen)</option>
    @elseif(str_starts_with($business->template_code ?? '', 'mfg_'))
        <option value="central_kitchen">Pabrik / Workshop Produksi</option>
    @elseif(($business->template_code ?? '') === 'service_contractor')
        <option value="central_kitchen">Basecamp / Workshop Proyek</option>
    @endif
    ```

#### Langkah 4.2: Penyaringan Tombol Header Tidak Relevan
* **Berkas:** [`resources/views/app/warehouse/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/index.blade.php)
* **Tindakan:**
  - Tautan *[Pengiriman Storefront]* dibungkus:
    ```blade
    @if(($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING) || $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT)) && (\App\Support\Context::hasPermission('storefront.shipping.manage') || \App\Support\Context::isAdminOrOwner()))
        {{-- Tombol Pengiriman Storefront --}}
    @endif
    ```
  - Tautan *[Katalog Bahan]* dibungkus:
    ```blade
    @if($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) && \App\Support\Context::hasPermission('inventory.view'))
        {{-- Tombol Katalog Bahan --}}
    @endif
    ```

#### Langkah 4.3: Pencegahan Double-Submit & Masking Ribuan
* **Berkas:** [`resources/views/app/warehouse/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/index.blade.php) & [`show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/show.blade.php)
* **Tindakan:**
  - Tambahkan `x-data="{ submitting: false }"` pada form submit modal.
  - Pasang `x-bind:disabled="submitting"` dan spinner pada tombol simpan utama.

---

### FASE 5: Pengujian Otomatis Lolos 100%, Verifikasi & Dokumentasi

**Tujuan:** Membuktikan hasil pekerjaan tanpa error (*zero-error mandate*) dan memperbarui riwayat pekerjaan.

#### Langkah 5.1: Pengujian Sintaks PHP
* **Perintah:**
  ```powershell
  php -l app/Http/Controllers/Web/Warehouse/WarehouseWebController.php
  php -l app/Http/Controllers/Web/Inventory/InventoryWebController.php
  php -l routes/owner.php
  php -l routes/auth.php
  ```

#### Langkah 5.2: Eksekusi Automated Tests
* **Perintah:**
  ```powershell
  php artisan test --filter=Warehouse
  php artisan test --filter=Inventory
  ```
* **Kriteria:** 100% lulus (0 failure, 0 error).

#### Langkah 5.3: Pencatatan Riwayat Pekerjaan AI (`docs/AiWorkHistory.md`)
* Catat entri lengkap dengan Work ID baru `[WORK-2026-09-27-187]` memuat 7 komponen wajib sesuai `docs/agent.md`.

---

## 4. Matriks Pembaruan Berkas Sumber Kode

| No | Berkas Sumber Kode | Tindakan | Ringkasan Modifikasi |
|:---:|---|:---:|---|
| 1 | [`app/Http/Controllers/Web/Inventory/InventoryWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Inventory/InventoryWebController.php) | Modify | Tenant scoping `Rule::exists()`, verifikasi PIN Supervisor, integrasi AutoJournal, audit log. |
| 2 | [`app/Http/Controllers/Web/Warehouse/WarehouseWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Warehouse/WarehouseWebController.php) | Modify | Dynamic entitlement check (outlet vs warehouse), guard nonaktifkan lokasi riwayat transaksi, audit log. |
| 3 | [`routes/auth.php`](file:///c:/laragon/www/cooca_core/routes/auth.php) | Modify | Penambahan middleware `throttle:60,1` pada route geocoding. |
| 4 | [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php) | Modify | Relaksasi middleware statis `entitlement:warehouse` agar didelegasikan dinamis di controller. |
| 5 | [`resources/views/app/warehouse/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/index.blade.php) | Modify | Gating tombol storefront & bahan baku, dinamisasi dropdown tipe lokasi 20 industri, proteksi double-submit. |
| 6 | [`resources/views/app/warehouse/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/warehouse/show.blade.php) | Modify | Dropdown alasan baku (`reason_code`), input PIN Supervisor pada modal penyesuaian, dinamisasi dropdown tipe lokasi. |

---

## 5. Manajemen Risiko & Strategi Rollback

| Potensi Risiko | Probabilitas | Dampak | Strategi Mitigasi & Rollback |
|---|:---:|:---:|---|
| **Staf lupa PIN Supervisor saat opname fisik** | Sedang | Sedang | Sediakan notifikasi ramah: *"Minta bantuan Pemilik Usaha / Supervisor untuk memasukkan PIN 6-digit"* dan opsi reset PIN di Pengaturan Keamanan. |
| **Merchant komplain lokasi tidak bisa dihapus** | Rendah | Rendah | Berikan penjelasan penenang jiwa: lokasi otomatis dinonaktifkan (`is_active = false`) agar tidak muncul di POS kasir namun pembukuan masa lalu tetap sah. |
| **Kegagalan Jurnal Akuntansi saat penyesuaian stok** | Rendah | Tinggi | Seluruh operasi penyesuaian stok dibungkus dalam `DB::transaction()` atomik; jika jurnal gagal terbit, pemotongan stok otomatis di-rollback tanpa meninggalkan data gantung (*zero orphan records*). |

---

## 6. Checklist Verifikasi Definition of Done (DoD)

- [x] Validasi IDOR `Rule::exists()` terpasang dan lolos pengujian unit.
- [x] Verifikasi `supervisor_pin` memblokir pengurangan stok $> \text{Rp } 100.000$ jika PIN salah.
- [x] Jurnal ganda (*Beban Kerugian Selisih Persediaan* vs *Persediaan*) terbit otomatis di General Ledger.
- [x] Opsi *"Dapur Pusat"* tersembunyi pada akun Bengkel Mobil, Apotek, dan Konveksi.
- [x] Tombol *"Pengiriman Storefront"* tersembunyi pada industri non-ekspedisi.
- [x] Menghapus lokasi yang memiliki riwayat mutasi otomatis beralih ke `is_active = false`.
- [x] Seluruh pengujian otomatis `php artisan test` lolos 100% (0 error, 0 failure).
- [x] Riwayat tercatat di `docs/AiWorkHistory.md`.
